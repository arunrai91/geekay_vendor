<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Adminhtml\System\Config\Field\Renderer;

use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\Source\SliderStyle as SliderStyleSource;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class SliderColor extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SliderStyleSource
     */
    private $sliderStyle;

    /**
     * @var Json
     */
    private $json;

    public function __construct(
        ConfigProvider $configProvider,
        SliderStyleSource $sliderStyle,
        Context $context,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->sliderStyle = $sliderStyle;
        $this->json = $json;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_Shopby::system/config/field/color.phtml');
    }

    public function getSliderStyleOptions(): string
    {
        return $this->json->serialize($this->sliderStyle->toArray());
    }

    public function getSliderStyle(): string
    {
        list($storeId, $scope) = $this->getScopeParams();

        return $this->configProvider->getSliderStyle($storeId, $scope);
    }

    public function getSliderColor(): string
    {
        list($storeId, $scope) = $this->getScopeParams();

        return $this->configProvider->getSliderColor($storeId, $scope);
    }

    private function getScopeParams()
    {
        $request = $this->getRequest();
        $scope = $request->getParam(ScopeInterface::SCOPE_STORE)
            ? ScopeInterface::SCOPE_STORE
            : ScopeInterface::SCOPE_WEBSITE;

        return [(int) $request->getParam($scope), $scope];
    }
}
