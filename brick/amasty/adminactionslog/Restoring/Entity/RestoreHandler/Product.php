<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring\Entity\RestoreHandler;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Catalog\Product as SaveHandlerProduct;
use Amasty\AdminActionsLog\Logging\Util\DetailsBuilder;
use Amasty\AdminActionsLog\Model\LogEntry\AdminLogEntryFactory;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Product extends Common
{
    public const CATEGORY_IDS_KEY = 'category_ids';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DetailsBuilder
     */
    private $detailsBuilder;

    /**
     * @var AdminLogEntryFactory
     */
    private $logEntryFactory;

    /**
     * @var LogEntryRepositoryInterface
     */
    private $logEntryRepository;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * Attributes that require immediate save instead of addAttributeUpdate
     *
     * @var string[]
     */
    private $directSaveAttrs;

    /**
     * @var CategoryLinkInterfaceFactory
     */
    private $categoryLinkFactory;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectDataStorageInterface $dataStorage,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        DetailsBuilder $detailsBuilder,
        AdminLogEntryFactory $logEntryFactory,
        LogEntryRepositoryInterface $logEntryRepository,
        array $directSaveAttrs = [],
        ?ProductResource $productResource = null,
        ?CategoryLinkInterfaceFactory $categoryLinkFactory = null
    ) {
        parent::__construct($objectManager, $dataStorage, $storeManager);
        $this->productRepository = $productRepository;
        $this->detailsBuilder = $detailsBuilder;
        $this->logEntryFactory = $logEntryFactory;
        $this->logEntryRepository = $logEntryRepository;
        $this->directSaveAttrs = $directSaveAttrs;
        $this->productResource = $productResource ?? $objectManager->get(ProductResource::class);
        $this->categoryLinkFactory = $categoryLinkFactory ?? $objectManager->get(CategoryLinkInterfaceFactory::class);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function restore(LogEntryInterface $logEntry, array $logDetails): void
    {
        try {
            $storeId = $logEntry->getStoreId() ?? Store::DEFAULT_STORE_ID;
            $this->setCurrentStore($storeId);
            $productId = $logEntry->getData(LogEntry::ELEMENT_ID);
            $product = $this->productRepository->getById($productId, false, $storeId);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Unable to restore changes. Error: %1', $e->getMessage()));
        }

        if (!$product->hasData('store_id')) {
            $storeId = $logEntry->getStoreId() ?? Store::DEFAULT_STORE_ID;
            $product->setData('store_id', $storeId);
        }

        $needDirectSave = false;
        $beforeData = $afterData = [];
        foreach ($logDetails as $detail) {
            $oldValue = $detail->getOldValue();
            $elementKey = $detail->getName();
            $afterData[$elementKey] = $oldValue;
            $beforeData[$elementKey] = $product->getData($elementKey);

            if ($elementKey === self::CATEGORY_IDS_KEY) {
                $oldValue = explode(',', $oldValue ?? '');
                $product->setData('is_changed_categories', 1);
                $product->setData($elementKey, $oldValue);
                $this->setCategoryLinks($product);
                $beforeData[$elementKey] = implode(',', $beforeData[$elementKey]);
            }

            $product->setData($elementKey, $oldValue);
            if (in_array($elementKey, $this->directSaveAttrs, true)) {
                $needDirectSave = true;
            } elseif ($this->productResource->getAttribute($elementKey)) {
                $product->addAttributeUpdate($elementKey, $oldValue, $storeId);
            }
        }

        if ($needDirectSave) {
            $this->setRestoreActionFlag($product);
            $this->productResource->save($product);
        } else {
            $detailsList = $this->detailsBuilder->build(get_class($product), $beforeData, $afterData);
            if (!empty($detailsList)) {
                $logEntryData = [
                    LogEntry::TYPE => LogEntryTypes::TYPE_RESTORE,
                    LogEntry::ITEM => $product->getName(),
                    LogEntry::CATEGORY => SaveHandlerProduct::CATEGORY,
                    LogEntry::CATEGORY_NAME => __('Catalog Product'),
                    LogEntry::ELEMENT_ID => (int)$product->getId(),
                    LogEntry::STORE_ID => (int)$product->getStoreId()
                ];
                $logEntry = $this->logEntryFactory->create($logEntryData);
                $logEntry->setLogDetails($detailsList);
                $this->logEntryRepository->save($logEntry);
            }
        }
    }

    private function setCategoryLinks(ProductInterface $product): void
    {
        $extensionAttributes = $product->getExtensionAttributes();
        if (!$extensionAttributes) {
            return;
        }

        $categoryLinks = [];
        foreach ((array)$extensionAttributes->getCategoryLinks() as $categoryLink) {
            $categoryLinks[$categoryLink->getCategoryId()] = $categoryLink;
        }

        $newCategoryLinks = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            $categoryLink = $categoryLinks[$categoryId] ??
                $this->categoryLinkFactory->create()
                    ->setCategoryId($categoryId)
                    ->setPosition(0);
            $newCategoryLinks[] = $categoryLink;
        }

        $extensionAttributes->setCategoryLinks(!empty($newCategoryLinks) ? $newCategoryLinks : null);
        $product->setExtensionAttributes($extensionAttributes);
    }
}
