<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Model\Layer\FilterList;
use Amasty\Shopby\Model\Layer\GetFiltersExpanded;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\View\Element\Template\Context;

class FilterCollapsing extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Layer
     */
    private $catalogLayer;

    /**
     * @var FilterList
     */
    private $filterList;

    /**
     * @var GetFiltersExpanded
     */
    private $getFiltersExpanded;

    public function __construct(
        LayerResolver $layerResolver,
        FilterList $filterList,
        GetFiltersExpanded $getFiltersExpanded,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->catalogLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->getFiltersExpanded = $getFiltersExpanded;
    }

    /**
     * @return int[]
     */
    public function getFiltersExpanded(): array
    {
        return $this->getFiltersExpanded->execute($this->filterList->getFilters($this->catalogLayer));
    }
}
