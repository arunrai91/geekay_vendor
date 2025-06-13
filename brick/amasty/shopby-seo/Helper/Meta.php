<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Helper;

use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\Integration\ShopbySeo\GetConfigProvider;
use Amasty\ShopbySeo\Model\Request\RobotsRegistry;
use Amasty\ShopbySeo\Model\Source\IndexMode;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBase\Model\Integration\IntegrationFactory;
use Amasty\ShopbySeo\Model\ConfigProvider;

class Meta extends AbstractHelper
{
    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $dataHelper;

    /**
     * @var boolean
     */
    private $isFollowingAllowed;

    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var IntegrationFactory
     */
    private $integrationFactory;

    /**
     * @var ConfigProvider|null
     */
    private $configProvider;

    /**
     * @var FilterRequestDataResolver|null
     */
    private $filterRequestDataResolver;

    /**
     * @var RobotsRegistry
     */
    private RobotsRegistry $robotsRegistry;

    public function __construct(
        Context $context,
        \Amasty\Shopby\Helper\Data $dataHelper,
        \Magento\Framework\App\Request\Http $request,
        IntegrationFactory $integrationFactory,
        GetConfigProvider $getConfigProvider,
        FilterRequestDataResolver $filterRequestDataResolver,
        RobotsRegistry $robotsRegistry
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->request = $request;
        $this->integrationFactory = $integrationFactory;
        $this->configProvider = $getConfigProvider->execute();
        $this->filterRequestDataResolver = $filterRequestDataResolver;
        $this->robotsRegistry = $robotsRegistry;
    }

    /**
     * @param PageConfig $pageConfig
     * @return void
     */
    public function setPageTags(PageConfig $pageConfig)
    {
        $this->pageConfig = $pageConfig;
        if ($this->isModifyRobotsEnable()) {
            $this->setRobots();
        }
    }

    /**
     * @return void
     */
    private function setRobots()
    {
        $follow = $this->getFollowTagValue();
        $robots = $this->pageConfig->getRobots();
        $robots = $this->getIndexTagValue() ? $robots : preg_replace('/\w*index/i', 'noindex', $robots);
        if ($this->configProvider && $this->configProvider->isEnableRelNofollow()) {
            $robots = $follow ? $robots : preg_replace('/\w*follow/i', 'nofollow', $robots);
        }
        $this->isFollowingAllowed = $follow;
        $this->pageConfig->setRobots($robots);
        $this->robotsRegistry->set($robots);
    }

    /**
     * @return bool
     */
    public function getIndexTagValue()
    {
        $appliedFiltersSettings = $this->getRelevanceFilters();
        $index = $this->getIndexTag($appliedFiltersSettings);
        foreach ($appliedFiltersSettings as $row) {
            if (!$index) {
                break;
            }

            $data = new DataObject([
                'setting' => $row['setting'],
                'filter' => $row['filter']
            ]);
            $index = $index ? $this->getIndexTagByData($index, $data) : $index;
        }

        return $index;
    }

    /**
     * @return bool
     */
    public function getFollowTagValue()
    {
        $appliedFiltersSettings = $this->getRelevanceFilters();
        $follow = $this->getFollowTag();
        foreach ($appliedFiltersSettings as $row) {
            if (!$follow) {
                break;
            }

            $data = new DataObject([
                'setting' => $row['setting'],
                'filter' => $row['filter']
            ]);
            $follow = $follow ? $this->getFollowTagByData($follow, $data) : $follow;
        }

        return $follow;
    }

    /**
     * @return array
     */
    private function getRelevanceFilters()
    {
        $appliedFiltersSettings = $this->dataHelper->getSelectedFiltersSettings();
        if ($this->dataHelper->isBrandPage()) {
            $brandKey = $this->getShopbyBrandHelper()->getBrandAttributeKey($appliedFiltersSettings);
            if ($brandKey !== null) {
                unset($appliedFiltersSettings[$brandKey]);
            }
        }

        return $appliedFiltersSettings;
    }

    /**
     * @return mixed
     */
    private function getShopbyBrandHelper()
    {
        return $this->integrationFactory->get(\Amasty\ShopbyBrand\Helper\Data::class, true);
    }

    /**
     * @param array[] $appliedFiltersSettings
     * @return bool
     */
    private function getIndexTag(array $appliedFiltersSettings)
    {
        $result = true;
        if ($this->request->getParam('p', 0) > 0) {
            $noIndexPagedCategory = $this->scopeConfig->getValue(
                'amasty_shopby_seo/robots/noindex_paginated',
                ScopeInterface::SCOPE_STORE
            );
            $result = !$noIndexPagedCategory;
        }

        if ($result) {
            $isNoIndexWithMultiple = $this->scopeConfig->getValue(
                'amasty_shopby_seo/robots/noindex_multiple',
                ScopeInterface::SCOPE_STORE
            );
            if ($isNoIndexWithMultiple && count($appliedFiltersSettings) > 1) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function getFollowTag()
    {
        return true;
    }

    /**
     * Enhanced in plugins.
     *
     * @param bool $indexTag
     * @param DataObject $data
     * @return bool
     */
    public function getIndexTagByData($indexTag, DataObject $data)
    {
        return $this->getTagByData(FilterSettingInterface::INDEX_MODE, $indexTag, $data);
    }

    /**
     * Enhanced in plugins.
     *
     * @param bool $followTag
     * @param DataObject $data
     * @return bool
     */
    public function getFollowTagByData($followTag, DataObject $data)
    {
        return $this->getTagByData(FilterSettingInterface::FOLLOW_MODE, $followTag, $data);
    }

    /**
     * @param $tagKey
     * @param $tagValue
     * @param $data
     * @return bool
     */
    public function getTagByData($tagKey, $tagValue, $data)
    {
        if ($this->isModifyRobotsEnable()) {
            /** @var FilterSettingInterface $setting */
            $setting = $data['setting'];

            $mode = $tagKey == FilterSettingInterface::INDEX_MODE
                ? $setting->getIndexMode()
                : $setting->getFollowMode();

            if ($mode == IndexMode::MODE_NEVER || $this->isNofollowBySingleMode($data, $mode)) {
                $tagValue = false;
            }
        }

        return $tagValue;
    }

    /**
     * @param $data
     * @param string $mode
     * @return bool
     */
    private function isNofollowBySingleMode($data, $mode = '')
    {
        /** @var FilterInterface $filter */
        $filter = $data['filter'];
        $value = $this->filterRequestDataResolver->getFilterParam($filter);
        $optionCount = count(is_array($value) ? $value : explode(',', $value));
        $filterCount = count($this->dataHelper->getSelectedFiltersSettings());

        if ($this->dataHelper->isBrandPage()) {
            --$filterCount;
        }

        return $mode == IndexMode::MODE_SINGLE_ONLY && ($optionCount > 1 || $filterCount > 1);
    }

    /**
     * @return bool
     */
    public function isFollowingAllowed()
    {
        return $this->isFollowingAllowed;
    }

    /**
     * @return bool
     */
    public function isModifyRobotsEnable()
    {
        return $this->scopeConfig->isSetFlag('amasty_shopby_seo/robots/control_robots', ScopeInterface::SCOPE_STORE);
    }
}
