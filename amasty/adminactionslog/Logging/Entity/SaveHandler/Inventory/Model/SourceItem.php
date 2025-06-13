<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Inventory\Model;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Catalog\Product;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Inventory\Model\SourceItem as InventorySourceItem;

class SourceItem extends Common
{
    public const CATEGORY = 'catalog/product/edit/source/item';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ArrayFilter\ScalarValueFilter $scalarValueFilter,
        ArrayFilter\KeyFilter $keyFilter,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $scalarValueFilter,
            $keyFilter
        );
        $this->productRepository = $productRepository;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var InventorySourceItem $sourceItem */
        $sourceItem = $metadata->getObject();

        $logMetadata = [
            LogEntry::ITEM => $sourceItem->getSku(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Source Item (%1)', $sourceItem->getSourceCode()),
            LogEntry::ELEMENT_ID => $sourceItem->getId()
        ];

        try {
            $product = $this->productRepository->get($sourceItem->getSku());

            $logMetadata[LogEntry::VIEW_ELEMENT_ID] = $product->getId();
            $logMetadata[LogEntry::ITEM] = $product->getName();
        } catch (NoSuchEntityException $e) {
            null;
        }

        return $logMetadata;
    }
}
