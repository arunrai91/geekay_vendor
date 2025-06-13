<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring\Entity\RestoreHandler;

use Amasty\AdminActionsLog\Api\Data\LogDetailInterface;
use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductOption extends Common
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $productCustomOptionRepository;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectDataStorageInterface $dataStorage,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        ProductCustomOptionRepositoryInterface $productCustomOptionRepository
    ) {
        parent::__construct(
            $objectManager,
            $dataStorage,
            $storeManager
        );

        $this->productRepository = $productRepository;
        $this->productCustomOptionRepository = $productCustomOptionRepository;
    }

    public function restore(LogEntryInterface $logEntry, array $logDetails): void
    {
        if (empty($logDetails) || !is_array($logEntry->getAdditionalData())) {
            return;
        }

        $additionalData = $logEntry->getAdditionalData();
        $productId = $additionalData['productId'] ?? null;
        $optionId = $additionalData['optionId'] ?? null;
        if (!$productId || !$optionId) {
            return;
        }

        $product = $this->productRepository->getById($productId, false, (int)$logEntry->getStoreId());
        $option = $this->productCustomOptionRepository->getProductOptions($product)
            [$optionId] ?? null;

        if (!$option) {
            throw new NoSuchEntityException(__('Product Option with specified ID "%1" not found.', $optionId));
        }

        /** @var LogDetailInterface $logDetail */
        foreach ($logDetails as $logDetail) {
            $oldValue = $logDetail->getOldValue();
            $elementKey = $logDetail->getName();
            $option->setData($elementKey, $oldValue);
        }

        $option->setProductSku($product->getSku());
        $this->setRestoreActionFlag($option);
        // Workaround for \Magento\Catalog\Model\Product\Option\Repository::save()
        $this->setCurrentStore((int)$logEntry->getStoreId());
        $this->productCustomOptionRepository->save($option);
    }
}
