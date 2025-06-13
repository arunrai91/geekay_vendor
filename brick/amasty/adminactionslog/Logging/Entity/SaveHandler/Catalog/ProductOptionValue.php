<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Catalog;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;
use Amasty\AdminActionsLog\Logging\Util\ProductIdentifierResolver;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Amasty\AdminActionsLog\Restoring\Entity\RestoreHandler\AbstractHandler;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductOptionValue extends Common
{
    public const CATEGORY = 'catalog/product/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        'is_use_default',
        'initialize',
        'record_id',
        'store_id'
    ];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $productCustomOptionRepository;

    /**
     * @var ProductIdentifierResolver
     */
    private $productIdentifierResolver;

    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    public function __construct(
        ArrayFilter\ScalarValueFilter $scalarValueFilter,
        ArrayFilter\KeyFilter $keyFilter,
        ProductRepositoryInterface $productRepository,
        ProductCustomOptionRepositoryInterface $productCustomOptionRepository,
        ProductIdentifierResolver $productIdentifierResolver,
        ObjectDataStorageInterface $dataStorage
    ) {
        parent::__construct($scalarValueFilter, $keyFilter);

        $this->productRepository = $productRepository;
        $this->productCustomOptionRepository = $productCustomOptionRepository;
        $this->productIdentifierResolver = $productIdentifierResolver;
        $this->dataStorage = $dataStorage;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Catalog\Model\Product\Option\Value $value */
        $value = $metadata->getObject();
        // Workaround because there is no product SKU in the object when deleted
        $productId = $this->productIdentifierResolver->execute((int)$value->getOption()->getProductId());
        $type = $value->isObjectNew() ? LogEntryTypes::TYPE_NEW : LogEntryTypes::TYPE_EDIT;
        $storageKey = spl_object_id($value->getOption()) . '.' . AbstractHandler::STORAGE_CODE_PREFIX;
        if ($this->dataStorage->isExists($storageKey)) {
            $type = LogEntryTypes::TYPE_RESTORE;
        }

        return [
            LogEntry::TYPE => $type,
            LogEntry::ITEM => __(
                'Value "%1" of Option "%2"',
                $value->getTitle(),
                $value->getOption()->getTitle()
            ),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Product Customizable Option'),
            LogEntry::ELEMENT_ID => $productId,
            LogEntry::STORE_ID => (int)$value->getStoreId(),
            LogEntry::ADDITIONAL_DATA => [
                'productId' => $productId,
                'optionId' => $value->getOption()->getId(),
                'valueId' => $value->getId() ? (int)$value->getId() : null
            ]
        ];
    }

    public function processBeforeSave($object): array
    {
        if ($object->getId() && $object->getOption()->getId()) {
            $productId = $this->productIdentifierResolver->execute(
                (int)$object->getOption()->getProductId()
            );
            $product = $this->productRepository->getById($productId, false, (int)$object->getStoreId());
            $option = $this->productCustomOptionRepository->getProductOptions($product)
                [$object->getOption()->getId()] ?? null;
            $value = $option ? $option->getValues()[$object->getId()] ?? null : null;

            return $value ? $this->filterObjectData($value->getData()) : [];
        }

        return [];
    }
}
