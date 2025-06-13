<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Sitemap\ItemProvider;

use Amasty\ShopbyBase\Model\SitemapBuilder;
use Amasty\ShopbyBrand\Model\Brand\ListDataProvider\LoadItems;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Framework\UrlInterface;
use Magento\Sitemap\Model\SitemapItemInterface;

class Brand
{
    /**
     * @var SitemapBuilder
     */
    private $sitemapBuilder;

    /**
     * @var LoadItems
     */
    private $brandLoad;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        SitemapBuilder $sitemapBuilder,
        LoadItems $brandLoad,
        ConfigProvider $configProvider,
        UrlInterface $url
    ) {
        $this->sitemapBuilder = $sitemapBuilder;
        $this->brandLoad = $brandLoad;
        $this->configProvider = $configProvider;
        $this->urlBuilder = $url;
    }

    /**
     * @param int $storeId
     * @return array|SitemapItemInterface[]
     */
    public function getItems($storeId)
    {
        $storeId = (int) $storeId;
        $options = $this->brandLoad->getItems($storeId);
        if ($this->configProvider->isExcludeEmptySitemapBrand($storeId)) {
            foreach ($options as $key => $option) {
                if ($option->getCount() === 0) {
                    unset($options[$key]);
                }
            }
        }

        $this->prepareUrl($storeId, $options);

        return $this->sitemapBuilder->prepareItems($options, $storeId);
    }

    private function prepareUrl(int $storeId, array &$options): void
    {
        $baseUrlLength = strlen($this->urlBuilder->getBaseUrl(['_scope' => $storeId]));
        foreach ($options as $option) {
            $option->setUrl(substr($option->getUrl(), $baseUrlLength));
        }
    }
}
