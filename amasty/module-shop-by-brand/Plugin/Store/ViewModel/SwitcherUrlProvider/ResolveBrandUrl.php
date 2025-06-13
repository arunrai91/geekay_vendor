<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Store\ViewModel\SwitcherUrlProvider;

use Amasty\ShopbyBase\Api\UrlBuilder\AdapterInterface as UrlAdapterInterface;
use Amasty\ShopbyBase\Helper\Data;
use Amasty\ShopbyBrand\Model\BrandResolver;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface as CoreUrlBuilder;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\ViewModel\SwitcherUrlProvider;

class ResolveBrandUrl
{
    /**
     * @var BrandResolver
     */
    private $brandResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlAdapterInterface
     */
    private $urlAdapter;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var CoreUrlBuilder
     */
    private $coreUrlBuilder;

    public function __construct(
        BrandResolver $brandResolver,
        ConfigProvider $configProvider,
        UrlAdapterInterface $urlAdapter,
        DataPersistorInterface $dataPersistor,
        Emulation $emulation,
        StoreManagerInterface $storeManager,
        EncoderInterface $encoder,
        CoreUrlBuilder $coreUrlBuilder
    ) {
        $this->brandResolver = $brandResolver;
        $this->configProvider = $configProvider;
        $this->urlAdapter = $urlAdapter;
        $this->dataPersistor = $dataPersistor;
        $this->emulation = $emulation;
        $this->storeManager = $storeManager;
        $this->encoder = $encoder;
        $this->coreUrlBuilder = $coreUrlBuilder;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetTargetStoreRedirectUrl(
        SwitcherUrlProvider $subject,
        callable $proceed,
        Store $store
    ): string {
        if ($this->brandResolver->getCurrentBrand() === null
            || $this->isBrandAttributeDifferentForStore((int)$store->getId())
            || $this->isBrandUrlKeySame((int)$store->getId())
        ) {
            return $proceed($store);
        }

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_scope'] = $store->getId();
        $params['_query'] = ['_' => null, 'shopbyAjax' => null, 'amshopby' => null];

        $this->emulation->startEnvironmentEmulation($store->getStoreId(), Area::AREA_FRONTEND, true);
        $this->dataPersistor->set(Data::SHOPBY_SWITCHER_STORE_ID, $store->getId());
        $currentUrl = $this->urlAdapter->getUrl('*/*/*', $params, true);
        $this->dataPersistor->clear(Data::SHOPBY_SWITCHER_STORE_ID);
        $this->emulation->stopEnvironmentEmulation();

        $redirectData[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->encoder->encode($currentUrl);
        $redirectData['___from_store'] = $this->storeManager->getStore()->getCode();
        $redirectData['___store'] = $store->getCode();

        return $this->coreUrlBuilder->getUrl('stores/store/redirect', $redirectData);
    }

    private function isBrandAttributeDifferentForStore(int $storeId): bool
    {
        return $this->configProvider->getBrandAttributeCode($storeId)
            !== $this->configProvider->getBrandAttributeCode((int)$this->storeManager->getStore()->getId());
    }

    private function isBrandUrlKeySame(int $storeId): bool
    {
        return $this->configProvider->getBrandUrlKey($storeId)
            === $this->configProvider->getBrandUrlKey((int)$this->storeManager->getStore()->getId());
    }
}
