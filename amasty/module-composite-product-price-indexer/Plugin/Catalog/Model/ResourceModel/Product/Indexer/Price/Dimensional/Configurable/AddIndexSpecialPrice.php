<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Composite Product Price Indexer
 */

// phpcs:ignore Generic.Files.LineLength
namespace Amasty\CompositeProductPriceIndexer\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price\Dimensional\Configurable;

use Amasty\CompositeProductPriceIndexer\Model\ModuleConflictResolver;
use Amasty\CompositeProductPriceIndexer\Model\ResourceModel\Catalog\Product\Indexer\Price\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable as ConfigurablePriceIndexer;

class AddIndexSpecialPrice
{
    /**
     * @var Configurable
     */
    private $configurableResource;

    /**
     * @var ModuleConflictResolver
     */
    private $moduleConflictResolver;

    public function __construct(
        Configurable $configurableResource,
        ModuleConflictResolver $moduleConflictResolver
    ) {
        $this->configurableResource = $configurableResource;
        $this->moduleConflictResolver = $moduleConflictResolver;
    }

    /**
     * @param ConfigurablePriceIndexer $subject
     * @param mixed $result
     * @param array $dimensions
     * @param \Traversable $entityIds
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteByDimensions(
        ConfigurablePriceIndexer $subject,
        $result,
        array $dimensions,
        \Traversable $entityIds
    ) {
        $entityIds = iterator_to_array($entityIds);

        if ($entityIds && !$this->moduleConflictResolver->hasConflicts()) {
            $this->configurableResource->addSpecialPrice($dimensions, $entityIds);
        }

        return $result;
    }
}
