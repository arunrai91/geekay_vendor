<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Store\Switcher;

use Amasty\Shopby\Plugin\Store\ViewModel\SwitcherUrlProvider\ModifyUrlData;
use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Amasty\ShopbyBase\Helper\Data;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Registry;
use Magento\Framework\Url\EncoderInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

class ModifySwitcherPostData
{
    public const STORE_PARAM_NAME = '___store';

    /**
     * @var UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var PostHelper
     */
    private $postHelper;

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
     * @var Registry
     */
    private $registry;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        UrlBuilderInterface $urlBuilder,
        EncoderInterface $encoder,
        PostHelper $postHelper,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        Emulation $emulation,
        Registry $registry,
        RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->encoder = $encoder;
        $this->postHelper = $postHelper;
        $this->storeManager = $storeManager;
        $this->dataPersistor = $dataPersistor;
        $this->emulation = $emulation;
        $this->registry = $registry;
        $this->request = $request;
    }

    /**
     * @param \Magento\Store\Block\Switcher $subject
     * @param \Closure $closure
     * @param $store
     * @param array $data
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetTargetStorePostData(
        \Magento\Store\Block\Switcher $subject,
        \Closure $closure,
        \Magento\Store\Model\Store $store,
        $data = []
    ) {
        $this->emulation->startEnvironmentEmulation(
            $store->getStoreId(),
            \Magento\Framework\App\Area::AREA_FRONTEND,
            true
        );

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = ['_' => null, 'shopbyAjax' => null, 'amshopby' => null];
        $params['_scope'] = $store->getId();

        $this->dataPersistor->set(Data::SHOPBY_SWITCHER_STORE_ID, $store->getId());
        $currentUrl = $this->urlBuilder->getUrl('*/*/*', $params, true);
        $this->dataPersistor->clear(Data::SHOPBY_SWITCHER_STORE_ID);

        $this->emulation->stopEnvironmentEmulation();

        $data[self::STORE_PARAM_NAME] = $store->getCode();
        $data['___from_store'] = $this->storeManager->getStore()->getCode();
        $data[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->encoder->encode($currentUrl);
        if ($categoryId = $this->getCategoryId()) {
            $data[ModifyUrlData::CATEGORY_ID] = $categoryId;
        }

        $url = $subject->getUrl('stores/store/redirect');

        return $this->postHelper->getPostData($url, $data);
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
        return $category ? (int)$category->getId() : null;
    }
}
