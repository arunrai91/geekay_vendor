<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation\Widget;

use Amasty\Shopby\Model\ConfigProvider;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\FilterSetting\StoreSettingResolver;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Tooltip extends Template implements WidgetInterface, IdentityInterface
{
    /**
     * @var FilterSettingInterface|null
     */
    private ?FilterSettingInterface $filterSetting = null;

    /**
     * @var string
     */
    protected $_template = 'Amasty_Shopby::layer/widget/tooltip.phtml';

    /**
     * @var StoreSettingResolver
     */
    private $storeSettingResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Json
     */
    private $serializer;

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        StoreSettingResolver $storeSettingResolver,
        Json $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeSettingResolver = $storeSettingResolver;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
    }

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();

        if (!$this->hasData('cache_lifetime')) {
            $this->setData('cache_lifetime', 86400);
        }
    }

    public function getCacheKeyInfo()
    {
        return array_merge(parent::getCacheKeyInfo(), $this->getIdentities());
    }

    public function getIdentities(): array
    {
        return $this->getFilterSetting()->getIdentities();
    }

    /**
     * @param FilterSettingInterface $filterSetting
     * @return $this
     */
    public function setFilterSetting(FilterSettingInterface $filterSetting)
    {
        $this->filterSetting = $filterSetting;
        return $this;
    }

    /**
     * @return FilterSettingInterface
     */
    public function getFilterSetting()
    {
        return $this->filterSetting;
    }

    public function getTooltipUrl(): string
    {
        $url = $this->configProvider->getTooltipSrc();
        if ($url) {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $url = $baseUrl . $url;
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getTooltipTemplate()
    {
        return sprintf(
            '<span class="tooltip amshopby-filter-tooltip" title="{content}"><img src="%s" alt="{content}"></span>',
            $this->_escaper->escapeUrl($this->getTooltipUrl())
        );
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        if ($tooltip = $this->getFilterSetting()->getTooltip()) {
            $tooltip = strip_tags($this->storeSettingResolver->chooseStoreLabel($tooltip));
        }

        return $tooltip;
    }

    /**
     * @param  array|bool|float|int|null|string $valueToEncode
     * @return string
     */
    public function jsonEncode($valueToEncode)
    {
        return $this->serializer->serialize($valueToEncode);
    }
}
