<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Observer;

use Amasty\Shopby\Model\Category\CacheCategoryTree;
use Amasty\Shopby\Model\Category\ExtendedCategoryCollection;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CategorySaveAfter implements ObserverInterface
{
    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    public function __construct(
        CacheInterface $cache
    ) {
        $this->cache = $cache;
    }

    /**
     * unable to flush cache via Magento\Framework\App\Cache\FlushCacheByTags
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        if ($category->hasDataChanges()) {
            $this->cache->clean([CacheCategoryTree::CACHE_TAG]);
        }
        if ($category->dataHasChangedFor(\Amasty\Shopby\Model\Layer\Filter\Category::EXCLUDE_CATEGORY_FROM_FILTER)) {
            $this->cache->clean([ExtendedCategoryCollection::CACHE_TAG]);
        }
    }
}
