<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Composite Product Price Indexer
 */

// phpcs:ignore Generic.Files.LineLength
namespace Amasty\CompositeProductPriceIndexer\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price\Dimensional\Simple;

use Amasty\CompositeProductPriceIndexer\Model\ModuleConflictResolver;
use Amasty\CompositeProductPriceIndexer\Model\ResourceModel\Catalog\Product\Indexer\Price\Simple as SimpleResource;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\SimpleProductPrice;

class AddIndexRulePrice
{
    /**
     * @var SimpleResource
     */
    private $simpleResource;

    /**
     * @var ModuleConflictResolver
     */
    private $moduleConflictResolver;

    public function __construct(
        SimpleResource $simpleResource,
        ModuleConflictResolver $moduleConflictResolver
    ) {
        $this->simpleResource = $simpleResource;
        $this->moduleConflictResolver = $moduleConflictResolver;
    }

    /**
     * @param SimpleProductPrice $subject
     * @param mixed $result
     * @param array $dimensions
     * @param \Traversable $entityIds
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteByDimensions(
        SimpleProductPrice $subject,
        $result,
        array $dimensions,
        \Traversable $entityIds
    ) {
        if ($this->moduleConflictResolver->hasConflicts()) {
            return $result;
        }

        $this->simpleResource->addRulePrice($dimensions, iterator_to_array($entityIds));

        return $result;
    }
}
