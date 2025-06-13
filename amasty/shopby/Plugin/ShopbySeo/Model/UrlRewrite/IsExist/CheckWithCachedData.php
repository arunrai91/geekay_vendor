<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\ShopbySeo\Model\UrlRewrite\IsExist;

use Amasty\Shopby\Model\Category\CacheCategoryTree;
use Amasty\ShopbySeo\Model\UrlRewrite\IsExist;
use Closure;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;

class CheckWithCachedData
{
    /**
     * @var CacheCategoryTree
     */
    private CacheCategoryTree $cacheCategoryTree;

    public function __construct(CacheCategoryTree $cacheCategoryTree)
    {
        $this->cacheCategoryTree = $cacheCategoryTree;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        IsExist $subject,
        Closure $proceed,
        string $path,
        ?int $storeId = null,
        ?string $entityType = null,
        ?int $entityId = null
    ) {
        if ($entityType === CategoryUrlRewriteGenerator::ENTITY_TYPE && $entityId !== null) {
            $currentCategoryTree = $this->cacheCategoryTree->getCurrentCategoryTree();
            if ($currentCategoryTree && isset($currentCategoryTree->getCategories()[$entityId])) {
                return $currentCategoryTree->getCategories()[$entityId]->getRequestPath() === $path;
            }
        }

        return $proceed($path, $storeId, $entityType, $entityId);
    }
}
