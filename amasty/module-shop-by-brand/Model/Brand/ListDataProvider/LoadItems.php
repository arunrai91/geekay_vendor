<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Brand\ListDataProvider;

use Amasty\Base\Model\Di\Wrapper;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopbyBase\Model\OptionSettingFactory;
use Amasty\ShopbyBrand\Helper\Data as DataHelper;
use Amasty\ShopbyBrand\Model\Attribute;
use Amasty\ShopbyBrand\Model\Brand\BrandDataInterface;
use Amasty\ShopbyBrand\Model\Brand\BrandDataInterfaceFactory;
use Amasty\ShopbyBrand\Model\BrandSettingProvider;
use Amasty\ShopbyBrand\Model\ProductCount;
use Magento\Customer\Model\Context;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\App\Cache\Type\Collection as CollectionCache;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\SharedCatalog\Model\State as SharedCatalogState;

/**
 * Load, cache and hydrate Brand items data for output Blocks.
 */
class LoadItems
{
    /**
     * @var ProductCount
     */
    private $productCount;

    /**
     * @var BrandSettingProvider
     */
    private $brandSettingProvider;

    /**
     * @var OptionSettingFactory
     */
    private $optionSettingFactory;

    /**
     * @var Attribute
     */
    private $brandAttribute;

    /**
     * @var CollectionCache
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var int
     */
    private $cacheLifetime;

    /**
     * @var BrandDataInterfaceFactory
     */
    private $brandDataFactory;

    /**
     * @var DataHelper
     */
    private $helper;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var SharedCatalogState
     */
    private $state;

    public function __construct(
        ProductCount $productCount,
        BrandSettingProvider $brandSettingProvider,
        OptionSettingFactory $optionSettingFactory,
        Attribute $brandAttribute,
        BrandDataInterfaceFactory $brandDataFactory,
        CollectionCache $cache,
        SerializerInterface $serializer,
        DataHelper $helper,
        ?int $cacheLifetime = 86400,
        HttpContext $httpContext = null,// TODO move to not optional
        Wrapper $state = null
    ) {
        $this->productCount = $productCount;
        $this->brandSettingProvider = $brandSettingProvider;
        $this->optionSettingFactory = $optionSettingFactory;
        $this->brandAttribute = $brandAttribute;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->cacheLifetime = $cacheLifetime;
        $this->brandDataFactory = $brandDataFactory;
        $this->helper = $helper;
        // OM for backward compatibility
        $this->httpContext = $httpContext ?? ObjectManager::getInstance()->get(HttpContext::class);
        $this->state = $state ?? ObjectManager::getInstance()->create(
            Wrapper::class,
            [
                'name' => SharedCatalogState::class, // @phpstan-ignore class.notFound
                'isShared' => true,
                'isProxy' => true
            ]
        );
    }

    /**
     * @param int $storeId
     *
     * @return BrandDataInterface[]
     */
    public function getItems(int $storeId, ?int $customerGroupId = null): array
    {
        $data = $this->getData($storeId, $customerGroupId);
        return $this->hydrateItems($data);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getData(int $storeId, ?int $customerGroupId = null): array
    {
        $identifier = $this->getCacheKey($storeId, $customerGroupId);
        $data = $this->cache->load($identifier);
        if ($data !== false) {
            return $this->serializer->unserialize($data);
        }

        $options = $this->brandAttribute->getOptions($storeId);
        if ($options === null) {
            return [];
        }

        $data = [];

        foreach ($options as $option) {
            $optionValue = (int) $option->getValue();
            $setting = $this->brandSettingProvider->getItemByStoreIdAndValue($storeId, $optionValue)
                ?? $this->optionSettingFactory->create();

            $data[] = $this->extractData($option, $setting);
        }

        $this->cache->save(
            $this->serializer->serialize($data),
            $identifier,
            $this->getCacheTags(),
            $this->cacheLifetime
        );

        return $data;
    }

    /**
     * @param Option $option
     * @param OptionSettingInterface $setting
     *
     * @return array
     */
    private function extractData(Option $option, OptionSettingInterface $setting): array
    {
        return [
            OptionSettingInterface::IS_FEATURED => $setting->getIsFeatured(),
            OptionSettingInterface::META_TITLE => $setting->getMetaTitle(),
            OptionSettingInterface::META_DESCRIPTION => $setting->getMetaDescription(),
            OptionSettingInterface::META_KEYWORDS => $setting->getMetaKeywords(),
            OptionSettingInterface::TOP_CMS_BLOCK_ID => $setting->getTopCmsBlockId(),
            OptionSettingInterface::BOTTOM_CMS_BLOCK_ID => $setting->getBottomCmsBlockId(),
            BrandDataInterface::IS_SHOW_IN_WIDGET => $setting->getIsShowInWidget(),
            BrandDataInterface::IS_SHOW_IN_SLIDER => $setting->getIsShowInSlider(),
            BrandDataInterface::BRAND_ID => $option->getValue(),
            BrandDataInterface::LABEL => trim((string) ($setting->getLabel() ? : $option->getLabel())),
            BrandDataInterface::URL => $this->helper->getBrandUrl($option),
            BrandDataInterface::IMG => $setting->getSliderImageUrl(),
            BrandDataInterface::IMAGE => $setting->getImageUrl(),
            BrandDataInterface::ALT => $setting->getSmallImageAlt() ? : $setting->getLabel(),
            BrandDataInterface::DESCRIPTION => $setting->getDescription(true),
            BrandDataInterface::SHORT_DESCRIPTION => $setting->getShortDescription(),
            BrandDataInterface::COUNT => $this->productCount->get($setting->getValue()),
            BrandDataInterface::POSITION => $setting->getSliderPosition()
        ];
    }

    /**
     * @param array $data
     *
     * @return BrandDataInterface[]
     */
    private function hydrateItems(array $data): array
    {
        $items = [];
        foreach ($data as $itemData) {
            $items[] = $this->brandDataFactory->create(['data' => $itemData]);
        }

        return $items;
    }

    public function getCacheKey(int $storeId, ?int $customerGroupId = null): string
    {
        $key = 'amasty_shopby_brand_items_collection-store' . $storeId;
        if ($this->isCustomerGroupIdRequiredForCacheKey()) {
            if ($customerGroupId === null) {
                $customerGroupId = $this->getCurrentCustomerGroupId();
            }
            $key .= '-cust_gr' . $customerGroupId;
        }

        return $key;
    }

    public function isCustomerGroupIdRequiredForCacheKey(): bool
    {
        return $this->state->isEnabled();
    }

    private function getCurrentCustomerGroupId(): int
    {
        return (int)$this->httpContext->getValue(Context::CONTEXT_GROUP);
    }

    /**
     * @return string[]
     */
    public function getCacheTags(): array
    {
        $productAttribute = $this->brandAttribute->getAttribute();
        if ($productAttribute === null) {
            $tags = [];
        } else {
            $tags = $productAttribute->getIdentities();
        }

        $tags[] = OptionSetting::CACHE_TAG;

        return $tags;
    }
}
