<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Store\Model\ScopeInterface;

class SitemapBuilder
{
    public const XML_PATH_CATEGORY_PRIORITY = 'sitemap/category/priority';
    public const XML_PATH_CATEGORY_CHANGEFREQ = 'sitemap/category/changefreq';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SitemapItemInterfaceFactory
     */
    private $itemFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->itemFactory = $itemFactory;
    }

    /**
     * @param array $items
     * @param $storeId
     * @return \Magento\Framework\DataObject[]
     */
    public function prepareItems(array $items, $storeId)
    {
        return array_map(function ($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item->getUrl(),
                'priority' => $this->getPriority($storeId),
                'changeFrequency' => $this->getChangeFrequency($storeId),
            ]);
        }, $items);
    }

    private function getPriority($storeId): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    private function getChangeFrequency($storeId): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
