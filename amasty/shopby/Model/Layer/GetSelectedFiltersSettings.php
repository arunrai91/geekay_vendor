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
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;

class GetSelectedFiltersSettings
{
    /**
     * @var FilterList
     */
    private $filterList;

    /**
     * @var Request
     */
    private $shopbyRequest;

    /**
     * @var FilterSetting
     */
    private $settingHelper;

    /**
     * @var Resolver
     */
    private $layerResolver;

    public function __construct(
        FilterList $filterList,
        Request $shopbyRequest,
        FilterSetting $settingHelper,
        Resolver $layerResolver
    ) {
        $this->filterList = $filterList;
        $this->shopbyRequest = $shopbyRequest;
        $this->settingHelper = $settingHelper;
        $this->layerResolver = $layerResolver;
    }

    public function execute(): array
    {
        $filters = $this->filterList->getAllFilters($this->layerResolver->get());
        $result = [];
        foreach ($filters as $filter) {
            /** @var AbstractFilter $filter */
            $var = $filter->getRequestVar();
            if ($this->shopbyRequest->getParam($var) !== null) {
                $setting = $this->settingHelper->getSettingByLayerFilter($filter);
                $result[] = [
                    'filter' => $filter,
                    'setting' => $setting,
                ];
            }
        }

        return $result;
    }
}
