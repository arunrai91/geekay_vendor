<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Product\Attributes;

use Amasty\Base\Model\Serializer;
use Magento\Framework\Config\CacheInterface;

class AttributesCache
{
    public const KEY_VALUES = 'amfeed_attrs_values';
    public const KEY_TYPES = 'amfeed_attrs_types';
    public const KEY_USER_DEFINED = 'amfeed_attrs_user_defined';
    public const CACHE_TAG = 'amfeed_attrs';

    private const ATTRIBUTES_CACHE_LIFETIME = 1800;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var int|null
     */
    private $cacheLifetime;

    public function __construct(
        CacheInterface $cache,
        Serializer $serializer,
        ?int $cacheLifetime = self::ATTRIBUTES_CACHE_LIFETIME
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->cacheLifetime = $cacheLifetime;
    }

    public function save(string $key, array $value): void
    {
        $this->cache->save(
            $this->serializer->serialize($value),
            $key,
            [self::CACHE_TAG],
            $this->cacheLifetime
        );
    }

    public function get(string $key): ?array
    {
        $cachedValue = $this->cache->load($key);
        if ($cachedValue) {
            return $this->serializer->unserialize($cachedValue);
        }

        return null;
    }

    public function flush(?int $feedId = null): void
    {
        if ($feedId) {
            $this->cache->remove(self::KEY_VALUES . $feedId);
            $this->cache->remove(self::KEY_TYPES . $feedId);
            $this->cache->remove(self::KEY_USER_DEFINED . $feedId);
        } else {
            $this->cache->clean(
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                [self::CACHE_TAG]
            );
        }
    }
}
