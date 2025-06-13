<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\XmlSitemap\Source;

use Amasty\ShopbyBrand\Helper\Data as Helper;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Amasty\ShopbyBrand\Model\ProductCount;
use Amasty\ShopbyBrand\Model\XmlSitemap\ConfigProvider as SitemapConfigProvider;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Store\Model\StoreManagerInterface;

class Brand
{
    public const ENTITY_CODE = 'amasty_shopbybrand';

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var SitemapConfigProvider
     */
    private $sitemapConfigProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $data;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ProductCount
     */
    private $productCount;

    /**
     * @var array
     */
    private $languageCodes;

    public function __construct(
        Helper $helper,
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        SitemapConfigProvider $sitemapConfigProvider,
        ConfigProvider $configProvider,
        ProductCount $productCount,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->eavConfig = $eavConfig;
        $this->sitemapConfigProvider = $sitemapConfigProvider;
        $this->storeManager = $storeManager;
        $this->data = $data;
        $this->configProvider = $configProvider;
        $this->productCount = $productCount;
    }

    public function getData($sitemap): \Generator
    {
        /** @var \Amasty\XmlSitemap\Model\Sitemap\SitemapEntityData $sitemapEntityData */
        $sitemapEntityData = $sitemap->getEntityData($this->getEntityCode());
        $storeId = $sitemap->getStoreId();

        $isExcludeEmptySitemapBrand = $this->configProvider->isExcludeEmptySitemapBrand($storeId);
        foreach ($this->getBrands() as $brand) {
            if ($optionId = $brand->getValue()) {
                if ($isExcludeEmptySitemapBrand && $this->productCount->get($optionId) === 0) {
                    continue;
                }
                $data = [
                    'loc' => $this->helper->getBrandUrl($brand, $storeId),
                    'frequency' => $sitemapEntityData->getFrequency(),
                    'priority' => $sitemapEntityData->getPriority()
                ];
                if ($this->isAddHreflang()) {
                    $data = $this->addHreflangs($data, $brand);
                } else {
                    $data = [$data];
                }

                yield $data;
            }
        }
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]
     */
    private function getBrands(): array
    {
        $options = [];
        $attributeCode = $this->helper->getBrandAttributeCode();

        if ($attributeCode) {
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);
            $options = $attribute->getOptions();
        }

        return $options;
    }

    public function getEntityCode(): string
    {
        return self::ENTITY_CODE;
    }

    public function getEntityLabel(): string
    {
        return __('Amasty Brands')->render();
    }

    private function addHreflangs(array $data, Option $brand): array
    {
        foreach ($this->getHreflangs($brand) as $hreflangs) {
            foreach ($hreflangs as $hreflang) {
                $data['hreflang'] = $hreflangs;
                $data['loc'] = $hreflang['attributes']['href'];
                $result[] = $data;
            }
        }

        return $result ?? [$data];
    }

    private function getHreflangs(Option $brand): array
    {
        foreach ($this->storeManager->getStores() as $storeView) {
            if ((bool) $storeView->getIsActive()) {
                $storeId = (int) $storeView->getStoreId();
                $result[$brand->getValue()][] = [
                    'attributes' => [
                        'hreflang' => $this->getLanguageCode($storeId),
                        'rel' => 'alternate',
                        'href' => $this->helper->getBrandUrl($brand, $storeId)
                    ]
                ];
            }
        }

        return $result ?? [];
    }

    public function isAddHreflang(): bool
    {
        return $this->sitemapConfigProvider->isBrandHreflang();
    }

    private function getLanguageCode(int $storeId): string
    {
        if (!isset($this->languageCodes)) {
            $this->languageCodes = $this->data['language_code_provider']->getData($storeId);
        }

        return $this->languageCodes[$storeId];
    }
}
