<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Filter\Resolver;

use Amasty\Shopby\Model\Layer\Filter\Decimal;
use Amasty\Shopby\Model\Layer\Filter\IsNew;
use Amasty\Shopby\Model\Layer\Filter\OnSale;
use Amasty\Shopby\Model\Layer\Filter\Price;
use Amasty\Shopby\Model\Layer\Filter\Rating;
use Amasty\Shopby\Model\Layer\Filter\Stock;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\FilterSetting;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class FilterSettingResolver
{
    /**
     * @var IsMultiselect
     */
    private $isMultiselect;

    /**
     * @var FilterSetting\FilterResolver
     */
    private $filterResolver;

    public function __construct(
        IsMultiselect $isMultiselect,
        \Amasty\ShopbyBase\Model\FilterSetting\FilterResolver $filterResolver
    ) {
        $this->isMultiselect = $isMultiselect;
        $this->filterResolver = $filterResolver;
    }

    public function isMultiselectAllowed(FilterInterface $filter): bool
    {
        switch (true) {
            case $filter instanceof Stock:
            case $filter instanceof Rating:
            case $filter instanceof OnSale:
            case $filter instanceof IsNew:
                $isMultiselectAllowed = false;
                break;
            case $filter instanceof Price:
            case $filter instanceof Decimal:
                $isMultiselectAllowed = true;
                break;
            default:
                $isMultiselectAllowed = $this->isMultiSelect($filter);
        }

        return $isMultiselectAllowed;
    }

    /**
     * @deprecated proxy method that doing nothing uniq
     * @see \Amasty\ShopbyBase\Model\FilterSetting\FilterResolver::resolveByFilter
     */
    public function getFilterSetting(FilterInterface $filter): FilterSettingInterface
    {
        return $this->filterResolver->resolveByFilter($filter);
    }

    private function isMultiSelect(FilterInterface $filter): bool
    {
        $filterSetting = $this->filterResolver->resolveByFilter($filter);

        return $filterSetting !== null && $this->isMultiselect->execute(
            $filterSetting->getAttributeCode(),
            $filterSetting->isMultiselect(),
            $filterSetting->getDisplayMode()
        );
    }
}
