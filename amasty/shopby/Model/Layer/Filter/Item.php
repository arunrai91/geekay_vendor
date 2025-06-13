<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby\Helper\UrlBuilder;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterSettingResolver;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Pager;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @var  UrlBuilder
     */
    private $urlBuilderHelper;

    /**
     * @var FilterSettingResolver
     */
    private $filterSettingResolver;

    public function __construct(
        UrlInterface $url,
        Pager $htmlPagerBlock,
        UrlBuilder $urlBuilderHelper,
        FilterSettingResolver $filterSettingResolver,
        array $data = []
    ) {
        parent::__construct($url, $htmlPagerBlock, $data);
        $this->urlBuilderHelper = $urlBuilderHelper;
        $this->filterSettingResolver = $filterSettingResolver;
    }

    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $this->getValue());
    }

    /**
     * Get url for remove item from filter
     * @param mixed $value
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRemoveUrl($value = null)
    {
        $value = $value ?? $this->getValue();

        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $value);
    }

    /**
     * @return bool
     */
    public function isAddNofollow()
    {
        $filterSetting = $this->filterSettingResolver->getFilterSetting($this->getFilter());

        if ($this->getFilter() instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
            return $filterSetting->isMultiselect() && $filterSetting->isAddNofollow();
        }

        return $filterSetting->isAddNofollow();
    }

    /**
     * @return string
     */
    public function getOptionLabel()
    {
        return $this->getData('label');
    }
}
