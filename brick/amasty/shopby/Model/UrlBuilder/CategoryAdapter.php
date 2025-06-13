<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\UrlBuilder;

use Amasty\Shopby\Model\Category\CacheCategoryTree;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;

class CategoryAdapter implements \Amasty\ShopbyBase\Api\UrlBuilder\AdapterInterface
{
    public const SELF_ROUTE_PATH = 'catalog/category/view';

    /**
     * @var \Magento\Framework\Url
     */
    private $urlBuilder;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CacheCategoryTree
     */
    private CacheCategoryTree $cacheCategoryTree;

    public function __construct(
        \Magento\Framework\Url $urlBuilder,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        CacheCategoryTree $cacheCategoryTree
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
        $this->cacheCategoryTree = $cacheCategoryTree;
    }

    /**
     * @param null $routePath
     * @param null $routeParams
     * @return string|null
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        $routePath = trim($routePath, '/');
        if ($routePath == self::SELF_ROUTE_PATH && isset($routeParams['id'])) {
            try {
                $categoryId = (int)$routeParams['id'];
                $requestPath = $this->getFromCache($categoryId);
                if ($requestPath === null) {
                    $requestPath = $this->getFromDb($categoryId);
                }

                if ($requestPath) {
                    if (isset($routeParams['_scope'])) {
                        $this->urlBuilder->setScope($routeParams['_scope']);
                    } else {
                        $this->urlBuilder->setScope(null);
                    }
                    $routeParams['_direct'] = $requestPath;
                    $routePath = '';
                    return $this->urlBuilder->getUrl($routePath, $routeParams);
                }
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }
        return null;
    }

    public function isApplicable(?string $routePath = null, ?array $routeParams = null): bool
    {
        $routePath = trim($routePath, '/');
        return $routePath == self::SELF_ROUTE_PATH && isset($routeParams['id']);
    }

    private function getFromCache(int $categoryId): ?string
    {
        $currentCategoryTree = $this->cacheCategoryTree->getCurrentCategoryTree();
        if ($currentCategoryTree && isset($currentCategoryTree->getCategories()[$categoryId])) {
            return $currentCategoryTree->getCategories()[$categoryId]->getRequestPath();
        }

        return null;
    }

    private function getFromDb(int $categoryId): ?string
    {
        $rewrite = $this->urlFinder->findOneByData([
            UrlRewrite::ENTITY_ID => $categoryId,
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::STORE_ID => $this->storeManager->getStore()->getId()
        ]);

        if ($rewrite) {
            return $rewrite->getRequestPath();
        }

        return null;
    }
}
