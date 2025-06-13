<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Store\ViewModel\SwitcherUrlProvider;

use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\Url\EncoderInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Amasty\ShopbyBase\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface as CoreUrlInterface;

class ModifyUrlData
{
    public const STORE_PARAM_NAME = '___store';
    public const FROM_STORE_PARAM_NAME = '___from_store';
    public const CMS_MODULE_NAME = 'cms';
    public const BRAND_ROUTE_NAME = 'ambrand';
    public const CATEGORY_ID = 'am_category_id';

    /**
     * @var UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var CoreUrlInterface
     */
    private $coreUrlBuilder;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        UrlBuilderInterface $urlBuilder,
        CoreUrlInterface $coreUrlBuilder,
        EncoderInterface $encoder,
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        Registry $registry
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->coreUrlBuilder = $coreUrlBuilder;
        $this->encoder = $encoder;
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param Store $store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetTargetStoreRedirectUrl($subject, callable $proceed, Store $store)
    {
        $this->emulation->startEnvironmentEmulation(
            $store->getStoreId(),
            \Magento\Framework\App\Area::AREA_FRONTEND,
            true
        );

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_scope'] = $store->getId();
        $params['_query'] = ['_' => null, 'shopbyAjax' => null, 'amshopby' => null];
        $this->dataPersistor->set(Data::SHOPBY_SWITCHER_STORE_ID, $store->getId());

        $redirectData = [self::STORE_PARAM_NAME => $store->getCode()];
        if ($this->request->getModuleName() === self::CMS_MODULE_NAME) {
            $currentUrl = $store->getCurrentUrl(false);
        } elseif ($this->request->getModuleName() === self::BRAND_ROUTE_NAME) {
            $currentUrl = $store->getUrl('*/*/*', $params);
        } else {
            $currentUrl = $this->urlBuilder->getUrl('*/*/*', $params, true);
            if ($categoryId = $this->getCategoryId()) {
                $redirectData[self::CATEGORY_ID] = $categoryId;
            }
        }

        $this->dataPersistor->clear(Data::SHOPBY_SWITCHER_STORE_ID);
        $this->emulation->stopEnvironmentEmulation();

        $redirectData[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->encoder->encode($currentUrl);
        $redirectData[self::FROM_STORE_PARAM_NAME] = $this->storeManager->getStore()->getCode();

        return $this->coreUrlBuilder->getUrl('stores/store/redirect', $redirectData);
    }

    private function getCategoryId(): ?int
    {
        if (!in_array($this->request->getFullActionName(), [
            'catalog_category_view',
            \Amasty\Shopby\Helper\Data::AMSHOPBY_INDEX_INDEX
        ])) {
            return null;
        }

        $category = $this->registry->registry('current_category');
        return $category ? (int) $category->getId() : null;
    }
}
