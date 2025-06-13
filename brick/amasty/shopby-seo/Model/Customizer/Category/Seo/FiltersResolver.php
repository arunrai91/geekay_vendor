<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\Customizer\Category\Seo;

use Amasty\Shopby\Model\Layer\Filter\Attribute;
use Amasty\Shopby\Model\Layer\FilterList;
use Amasty\Shopby\Model\Request;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\LayeredNavigation\Block\Navigation;

/**
 * Resolve layered navigation filters.
 */
class FiltersResolver
{
    /**
     * @var Navigation
     */
    private $navigationBlock;

    /**
     * @var FilterInterface[]
     */
    private $appliedFilters;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Request
     */
    private $amshopbyRequest;

    /**
     * @var FilterList
     */
    private $filterList;

    public function __construct(
        LayoutInterface $layout,
        Request $amshopbyRequest,
        FilterList $filterList
    ) {
        $this->layout = $layout;
        $this->amshopbyRequest = $amshopbyRequest;
        $this->filterList = $filterList;
    }

    public function getNavigationBlock(): ?Navigation
    {
        if ($this->navigationBlock === null) {
            foreach ($this->layout->getAllBlocks() as $block) {
                if ($block instanceof Navigation) {
                    $this->navigationBlock = $block;
                    break;
                }
            }
        }

        return $this->navigationBlock;
    }

    /**
     * Get filters which are used in "Now Shopping by"
     *
     * @return FilterInterface[]
     */
    public function getAppliedFilters(): array
    {
        if ($this->appliedFilters === null) {
            $this->appliedFilters = [];
            $filters = $this->getFilters();

            foreach ($filters as $filter) {
                if ($this->getAppliedFilterValue($filter)) {
                    // Request var should be uniq for each filter.
                    $this->appliedFilters[$filter->getRequestVar()] = $filter;
                }
            }
        }

        return $this->appliedFilters;
    }

    /**
     * @return AbstractFilter[]
     */
    private function getFilters(): array
    {
        $navigationBlock = $this->getNavigationBlock();
        $filters = [];

        if ($navigationBlock) {
            $layer = $navigationBlock->getLayer();
            $filters = $this->filterList->getAllFilters($layer);
        }

        return $filters;
    }

    public function getFilterByCode(string $attributeCode): ?Attribute
    {
        foreach ($this->getAppliedFilters() as $filter) {
            if ($filter instanceof Attribute
                && $filter->getAttributeModel()
                && $filter->getAttributeModel()->getAttributeCode() === $attributeCode
            ) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getAppliedFilterValue(FilterInterface $filter): ?string
    {
        return $this->amshopbyRequest->getParam($filter->getRequestVar());
    }
}
