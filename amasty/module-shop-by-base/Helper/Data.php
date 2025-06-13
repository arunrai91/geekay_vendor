<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Helper;

use Amasty\ShopbyBase\Model\Integration\DummyObject;
use Amasty\ShopbySeo\Model\ConfigProvider;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Amasty\ShopbyBase\Model\Integration\IntegrationFactory;

class Data extends AbstractHelper
{
    public const SHOPBY_MODULE_NAME = 'Amasty_Shopby';

    public const SHOPBY_CATEGORY_INDEX = 'amasty_shopby_category_index';

    public const SHOPBY_SEO_PARSED_PARAMS = 'amasty_shopby_seo_parsed_params';

    public const SHOPBY_BRAND_POPUP = 'shopby_brand_popup';

    public const SHOPBY_SWITCHER_STORE_ID = 'shopby_switcher_store_id';

    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\Module\ModuleResource
     */
    private $moduleResource;

    /**
     * @var IntegrationFactory
     */
    private $integrationFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\Module\ModuleResource $moduleResource,
        IntegrationFactory $integrationFactory
    ) {
        parent::__construct($context);
        $this->moduleList = $moduleList;
        $this->moduleResource = $moduleResource;
        $this->integrationFactory = $integrationFactory;
    }

    /**
     * @return null
     */
    public function getShopbyVersion()
    {
        return $this->moduleResource->getDbVersion(self::SHOPBY_MODULE_NAME);
    }

    /**
     * @return bool
     */
    public function isShopbyInstalled()
    {
        return ($this->moduleList->getOne(self::SHOPBY_MODULE_NAME) !== null)
            && $this->getShopbyVersion();
    }

    /**
     * @return string
     */
    public function getBrandAttributeCode()
    {
        /** @var \Amasty\ShopbyBrand\Helper\Data|DummyObject $brandHelper */
        $brandHelper = $this->integrationFactory->get(\Amasty\ShopbyBrand\Helper\Data::class, true);

        return (string)$brandHelper->getBrandAttributeCode();
    }

    /**
     * @return string
     */
    public function getBrandUrlKey()
    {
        /** @var \Amasty\ShopbyBrand\Helper\Data|DummyObject $brandHelper */
        $brandHelper = $this->integrationFactory->get(\Amasty\ShopbyBrand\Helper\Data::class, true);

        return (string)$brandHelper->getBrandUrlKey();
    }

    /**
     * @return bool
     */
    public function isAddSuffixToShopby()
    {
        /** @var \Amasty\ShopbySeo\Helper\Data|DummyObject $urlHelper */
        $urlHelper = $this->integrationFactory->get(\Amasty\ShopbySeo\Helper\Url::class, true);

        return $urlHelper->isAddSuffixToShopby();
    }

    /**
     * @return bool
     */
    public function isEnableRelNofollow()
    {
        /** @var ConfigProvider|DummyObject $seoHelper */
        $seoConfigProvider = $this->integrationFactory->get(ConfigProvider::class, true);

        return $seoConfigProvider->isEnableRelNofollow();
    }
}
