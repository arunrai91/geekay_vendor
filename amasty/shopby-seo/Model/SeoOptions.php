<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model;

use Amasty\ShopbyBase\Model\Cache\Type;
use Amasty\ShopbySeo\Model\SeoOptionsModifier\SeoModifierInterface;
use Amasty\ShopbySeo\Model\SeoOptionsModifier\UniqueBuilder;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class SeoOptions
{
    public const CACHE_KEY = 'amshopby_seo_options_data';

    /**
     * @var Json
     */
    private $json;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var array|null
     */
    private $optionsSeoData = [];

    /**
     * @var StateInterface
     */
    private $cacheState;

    /**
     * @var SeoModifierInterface[]
     */
    private $modifiers;

    /**
     * @var UniqueBuilder
     */
    private $uniqueBuilder;

    /**
     * @param Json $json
     * @param StoreManagerInterface $storeManager
     * @param CacheInterface $cache
     * @param StateInterface $cacheState
     * @param UniqueBuilder $uniqueBuilder
     * @param SeoModifierInterface[] $modifiers
     */
    public function __construct(
        Json $json,
        StoreManagerInterface $storeManager,
        CacheInterface $cache,
        StateInterface $cacheState,
        UniqueBuilder $uniqueBuilder,
        array $modifiers = []
    ) {
        $this->json = $json;
        $this->storeManager = $storeManager;
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->modifiers = $modifiers;
        $this->uniqueBuilder = $uniqueBuilder;
    }

    public function getData(): array
    {
        $storeId = $this->getCurrentStoreId();
        if (!isset($this->optionsSeoData[$storeId]) && $this->cacheState->isEnabled(Type::TYPE_IDENTIFIER)) {
            $cached = $this->cache->load($this->getCacheKey());
            if ($cached !== false) {
                $this->optionsSeoData[$storeId] = $this->json->unserialize($cached);
            }
        }

        if (!isset($this->optionsSeoData[$storeId])) {
            $this->loadData();
        }

        return $this->optionsSeoData[$storeId];
    }

    private function getCacheKey(): string
    {
        return self::CACHE_KEY . $this->getCurrentStoreId();
    }

    private function getCurrentStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    public function loadData(): void
    {
        $storeId = $this->getCurrentStoreId();
        $this->optionsSeoData[$storeId] = [];
        $attributeIds = [];
        /** @var SeoModifierInterface $modifier */
        foreach ($this->modifiers as $modifier) {
            $modifier->modify($this->optionsSeoData, $storeId, $attributeIds);
        }

        $this->uniqueBuilder->clear();
        $this->saveCache();
    }

    private function saveCache(): void
    {
        if ($this->cacheState->isEnabled(Type::TYPE_IDENTIFIER)) {
            $this->cache->save(
                $this->json->serialize($this->optionsSeoData[$this->getCurrentStoreId()]),
                $this->getCacheKey(),
                [Type::CACHE_TAG]
            );
        }
    }
}
