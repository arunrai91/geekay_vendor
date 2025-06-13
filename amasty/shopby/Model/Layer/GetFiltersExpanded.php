<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer;

use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Model\Request;
use Amasty\Shopby\Model\Source\Expand;
use Amasty\Shopby\Model\Source\FilterPlacedBlock;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\Detection\MobileDetect;
use Magento\Catalog\Model\Layer\Resolver;

class GetFiltersExpanded
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $catalogLayer;

    /**
     * @var FilterList
     */
    private $filterList;

    /**
     * @var FilterSetting
     */
    private $filterSettingHelper;

    /**
     * @var Request
     */
    private $shopbyRequest;

    /**
     * @var MobileDetect
     */
    private $mobileDetect;

    /**
     * @var CustomFilters
     */
    private $customFilters;

    public function __construct(
        Resolver $layerResolver,
        FilterList $filterList,
        FilterSetting $filterSettingHelper,
        Request $shopbyRequest,
        MobileDetect $mobileDetect,
        CustomFilters $customFilters
    ) {
        $this->catalogLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->shopbyRequest = $shopbyRequest;
        $this->mobileDetect = $mobileDetect;
        $this->customFilters = $customFilters;
    }

    /**
     * @return int[]
     */
    public function execute(?array $filters = null): array
    {
        $listExpandedFilters = [];
        $filters = $filters ?? $this->getFilters();
        $position = 0;

        foreach ($filters as $filter) {
            if (!$filter->getItemsCount()) {
                continue;
            }

            $filterSetting = $this->filterSettingHelper->getSettingByLayerFilter($filter);
            $value = $this->shopbyRequest->getParam($filter->getRequestVar());
            if ($this->isExpanded($filterSetting) || (!empty($value) || is_numeric($value))) {
                $listExpandedFilters[] = $position;
            }

            if ($filterSetting->getBlockPosition() != FilterPlacedBlock::POSITION_TOP) {
                $position++;
            }
        }

        return $listExpandedFilters;
    }

    private function isExpanded(FilterSettingInterface $filterSetting): bool
    {
         $attributeCode = $filterSetting->getAttributeCode();
        if ($this->customFilters->isCustomFilter($attributeCode)) {
            $config = $this->customFilters->getConfig($attributeCode);
            $expandValue = isset($config[FilterSettingInterface::EXPAND_VALUE])
                ? (int) $config[FilterSettingInterface::EXPAND_VALUE]
                : Expand::AUTO_LABEL;
        } else {
            $expandValue = $filterSetting->isExpanded();
        }

        return ($expandValue == Expand::DESKTOP_LABEL && !$this->mobileDetect->isMobile())
            || $expandValue == Expand::DESKTOP_AND_MOBILE_LABEL;
    }

    /**
     * @return array|\Magento\Catalog\Model\Layer\Filter\AbstractFilter[]
     */
    protected function getFilters()
    {
        return $this->filterList->getFilters($this->catalogLayer);
    }
}
