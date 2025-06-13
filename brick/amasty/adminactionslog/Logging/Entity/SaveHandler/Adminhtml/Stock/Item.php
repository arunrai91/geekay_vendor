<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Adminhtml\Stock;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Model\Adminhtml\Stock\Item as StockItem;

class Item extends Common
{
    public const CATEGORY = 'catalog/product/edit/stock/item';

    /**
     * @var array
     */
    protected $stockItems = [];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    public function __construct(
        ArrayFilter\ScalarValueFilter $scalarValueFilter,
        ArrayFilter\KeyFilter $keyFilter,
        ProductRepositoryInterface $productRepository,
        StockItemRepositoryInterface $stockItemRepository
    ) {
        parent::__construct($scalarValueFilter, $keyFilter);
        $this->productRepository = $productRepository;
        $this->stockItemRepository = $stockItemRepository;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var StockItem $stockItem */
        $stockItem = $metadata->getObject();
        $product = $this->productRepository->getById((int)$stockItem->getProductId());

        return [
            LogEntry::ITEM => $product->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Stock Item'),
            LogEntry::ELEMENT_ID => $stockItem->getId(),
            LogEntry::VIEW_ELEMENT_ID => $product->getId(),
        ];
    }

    public function processBeforeSave($object): array
    {
        $result = [];

        if ($object->getId()) {
            $stockItem = $this->getStockItem((int)$object->getId());
            $result = $this->filterObjectData($stockItem->getData());
        }

        return $result;
    }

    private function getStockItem(int $stockItemId): StockItem
    {
        if (!isset($this->stockItems[$stockItemId])) {
            $this->stockItems[$stockItemId] = $this->stockItemRepository->get($stockItemId);
        }

        return $this->stockItems[$stockItemId];
    }
}
