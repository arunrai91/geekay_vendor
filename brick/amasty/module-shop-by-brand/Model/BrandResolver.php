<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Helper\OptionSetting;
use Amasty\ShopbyBrand\Model\UrlBuilder\Adapter;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resolve currently viewed brand.
 */
class BrandResolver
{
    private const SHOP_BY_AJAX = 'shopbyAjax';
    private const AMSHOPBY_MODULE_NAME = 'amshopby';

    /**
     * @var OptionSettingInterface
     */
    private $currentBrand = false;

    /**
     * @var  OptionSetting
     */
    private $optionHelper;

    /**
     * @var  StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        OptionSetting $optionHelper,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        RequestInterface $request
    ) {
        $this->optionHelper = $optionHelper;
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
        $this->request = $request;
    }

    /**
     * Checks is provided filter are brand attribute.
     */
    public function isBrandFilter(FilterInterface $filter): bool
    {
        $brand = $this->getCurrentBrand();

        return $brand && ($filter->getRequestVar() === $brand->getAttributeCode());
    }

    /**
     * Get Brand model of currently viewed brand.
     *
     * @return null|OptionSettingInterface null if not a brand view page or brand is unavailable
     */
    public function getCurrentBrand(): ?OptionSettingInterface
    {
        if ($this->currentBrand === false) {
            if ($this->checkControllerName()
                && $this->getBrandAttributeCode()
                && ($brandValue = $this->getBrandValue())
            ) {
                $this->currentBrand = $this->getBrandModel($brandValue);
            } else {
                $this->currentBrand = null;
            }
        }

        return $this->currentBrand;
    }

    private function getBrandModel(string $brandValue): OptionSettingInterface
    {
        return $this->optionHelper->getSettingByOption(
            $brandValue,
            $this->getBrandAttributeCode(),
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * Validation is a request from a brand page or from all-products page with ajax
     *
     * @see \Amasty\ShopbyBrand\Controller\Router::requestToBrandPage
     */
    private function checkControllerName(): bool
    {
        return $this->request->getModuleName() === Adapter::MODULE_NAME
            || ($this->request->getParam(self::SHOP_BY_AJAX)
                && $this->request->getModuleName() === self::AMSHOPBY_MODULE_NAME);
    }

    private function getBrandValue(): ?string
    {
        return $this->request->getParam($this->getBrandAttributeCode());
    }

    private function getBrandAttributeCode(): string
    {
        return $this->configProvider->getBrandAttributeCode();
    }
}
