<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Helper;

use Amasty\Shopby\Model\Layer\Filter\Price;
use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Amasty\Shopby\Model\Layer\Filter\Category as CategoryFilter;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Amasty\ShopbyBase\Helper\Data as BaseData;

class UrlBuilder extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var FilterSetting
     */
    private $filterSettingHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * @var Category
     */
    private $categoryHelper;

    /**
     * @var UrlBuilderInterface
     */
    private $amUrlBuilder;

    /**
     * @var IsMultiselect
     */
    private $isMultiselect;

    /**
     * @var string[]
     */
    private $excludedSingleSelectCategoryActions = [
        'catalogsearch_result_index',
        'ambrand_index_index',
        'amasty_xlanding_page_view'
    ];

    public function __construct(
        Context $context,
        Registry $registry,
        FilterSetting $filterSettingHelper,
        Category $categoryHelper,
        UrlBuilderInterface $urlBuilder,
        IsMultiselect $isMultiselect,
        array $excludedSingleSelectCategoryActions = []
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->categoryHelper = $categoryHelper;
        $this->amUrlBuilder = $urlBuilder;
        $this->isMultiselect = $isMultiselect;
        $this->excludedSingleSelectCategoryActions = array_merge(
            $this->excludedSingleSelectCategoryActions,
            $excludedSingleSelectCategoryActions
        );
    }

    /**
     * @param FilterInterface $filter
     * @param string|array $optionValue
     * @return string
     */
    public function buildUrl(FilterInterface $filter, $optionValue)
    {
        $this->filter = $filter;

        if ($filter instanceof Price && is_array($optionValue)) {
            $optionValue = implode('-', $optionValue);
        }

        $currentValues = $this->getCurrentValues();
        $resultValue = $this->calculateResultValue($optionValue, $currentValues);

        $query = $this->buildQuery($filter, $resultValue);
        $query['p'] = null;
        $query['shopbyAjax'] = null;
        $query['price-ranges'] = null;
        $query['_'] = null;
        if ($filter instanceof Price) {
            $query['df'] = null;
            $query['dt'] = null;
        }

        $route = '*/*/*';
        $params = [];

        $categoryId = null;
        if ($this->isSingleselectCategoryFilter($filter)) {
            $route = 'catalog/category/view';
            $categoryId = (int) $optionValue;
            $params['id'] = $categoryId;
            $query['cat'] = null;
        }

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $query;
        //fix urls like catalogsearch/result/index/price/10-20/?price=10-60&q=bag
        $params['price'] = null;

        return $this->amUrlBuilder->getUrl($route, $params, false, $categoryId);
    }

    /**
     * @param FilterInterface $filter
     * @return bool
     */
    public function isSingleselectCategoryFilter($filter)
    {
        return $filter instanceof CategoryFilter
            && !$filter->isMultiselect()
            && !in_array($this->_request->getFullActionName(), $this->excludedSingleSelectCategoryActions);
    }

    /**
     * @param $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, $params = [])
    {
        return parent::_getUrl($route, $params);
    }

    /**
     * @param FilterInterface $filter
     * @param $resultValue
     * @return array|mixed
     */
    public function buildQuery(FilterInterface $filter, $resultValue)
    {
        $query = $this->registry->registry(BaseData::SHOPBY_SEO_PARSED_PARAMS);
        if (!is_array($query)) {
            $query = [];
        }
        $query[$filter->getRequestVar()] = $resultValue;

        return $query;
    }

    /**
     * @return array
     */
    protected function getCurrentValues()
    {
        $filterCode = $this->filter->getRequestVar();

        $data = $this->_request->getParam($filterCode);

        if (empty($data) && !is_numeric($data)) {
            $params = $this->_request->getParams();
            $data = isset($params['amshopby'][$filterCode]) ? $params['amshopby'][$filterCode] : null;
        }

        if (empty($data) && !is_numeric($data)) {
            $values = [];
        } else {
            $values = is_array($data) ? $data : explode(',', (string) $data);
            foreach ($values as $key => $val) {
                if (empty($val) && !is_numeric($val)) {
                    unset($values[$key]);
                }
            }
        }

        return $values;
    }

    /**
     * @param $optionValue
     * @param array $currentValues
     * @return string|null
     */
    protected function calculateResultValue($optionValue, array $currentValues)
    {
        if ($optionValue === null || is_array($optionValue)) {
            return null;
        }
        $key = array_search($optionValue, $currentValues);

        if ($this->isMultiSelectAllowed()) {
            $result = $currentValues;
            if ($key !== false) {
                unset($result[$key]);
            } else {
                if ($this->filter instanceof CategoryFilter && $this->categoryHelper->isCategoryFilterExtended()) {
                    $parents = $this->filter->getItems()->getParentsAndChildrenByItemId($optionValue);
                    $result = array_diff($result, $parents);
                }
                $result[] = $optionValue;
            }
        } else {
            if ($key !== false) {
                $result = [];
            } else {
                $result = [$optionValue];
            }
        }

        $value = $result ? implode(',', $result) : null;
        return $value;
    }

    private function isMultiSelectAllowed(): bool
    {
        $filterSetting = $this->filterSettingHelper->getSettingByLayerFilter($this->filter);

        return $this->isMultiselect->execute(
            $filterSetting->getAttributeCode(),
            $filterSetting->isMultiselect(),
            $filterSetting->getDisplayMode()
        );
    }
}
