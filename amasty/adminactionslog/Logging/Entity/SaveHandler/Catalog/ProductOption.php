<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Catalog;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;
use Amasty\AdminActionsLog\Logging\Util\ProductIdentifierResolver;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductOption extends Common
{
    public const CATEGORY = 'catalog/product/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        'is_use_default',
        'initialize',
        'record_id',
        'product_sku',
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

    public function __construct(
        ArrayFilter\ScalarValueFilter $scalarValueFilter,
        ArrayFilter\KeyFilter $keyFilter,
        ProductRepositoryInterface $productRepository,
        ProductCustomOptionRepositoryInterface $productCustomOptionRepository,
        ProductIdentifierResolver $productIdentifierResolver
    ) {
        parent::__construct($scalarValueFilter, $keyFilter);

        $this->productRepository = $productRepository;
        $this->productCustomOptionRepository = $productCustomOptionRepository;
        $this->productIdentifierResolver = $productIdentifierResolver;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Catalog\Model\Product\Option $option */
        $option = $metadata->getObject();
        $type = $option->isObjectNew() ? LogEntryTypes::TYPE_NEW : LogEntryTypes::TYPE_EDIT;
        // Workaround because there is no product SKU in the object when deleted
        $productId = $this->productIdentifierResolver->execute((int)$option->getProductId());

        return [
            LogEntry::TYPE => $type,
            LogEntry::ITEM => __('Option "%1"', $option->getTitle()),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Product Customizable Option'),
            LogEntry::ELEMENT_ID => $productId,
            LogEntry::STORE_ID => (int)$option->getStoreId(),
            LogEntry::ADDITIONAL_DATA => [
                'productId' => $productId,
                'optionId' => $option->getId() ? (int)$option->getId() : null
            ]
        ];
    }

    public function processBeforeSave($object): array
    {
        if ($object->getId()) {
            $productId = $this->productIdentifierResolver->execute((int)$object->getProductId());
            $product = $this->productRepository->getById($productId, false, (int)$object->getStoreId());
            $option = $this->productCustomOptionRepository->getProductOptions($product)
                [$object->getId()] ?? null;

            return $option ? $this->filterObjectData($option->getData()) : [];
        }

        return [];
    }

    public function processAfterSave($object): array
    {
        $data = parent::processAfterSave($object);
        if ($object->hasValues()) {
            unset($data['price_type']);
        }

        return $data;
    }
}
