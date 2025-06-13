<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\ShopbySeo\Helper;

use Amasty\Shopby\Helper\Category;
use Amasty\ShopbyBase\Model\ConfigProvider as BaseConfigProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Url
{
    public const CATEGORY_FILTER_PARAM_NAME = 'cat';
    public const SHOPBY_EXTRA_PARAM = 'amshopby';

    /**
     * @var UrlRewrite[]
     */
    private $rewrites;

    /**
     * @var Category
     */
    private $shopbyCategoryHelper;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BaseConfigProvider
     */
    private $baseConfig;

    public function __construct(
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        Category $categoryHelper,
        BaseConfigProvider $baseConfig
    ) {
        $this->shopbyCategoryHelper = $categoryHelper;
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
        $this->baseConfig = $baseConfig;
    }

    /**
     * @param \Amasty\ShopbySeo\Helper\Url $subject
     * @param string $identifier
     * @param array $preparedSeoAliases
     * @return array
     */
    public function beforeModifySeoIdentifierByAlias($subject, $identifier, $preparedSeoAliases)
    {
        $allProductsIdentifier = $this->baseConfig->getAllProductsUrlKey();

        if ($allProductsIdentifier == $identifier && $subject->hasCategoryFilterParam()) {
            $categoryId = $subject->getParam(self::CATEGORY_FILTER_PARAM_NAME);

            if (is_array($categoryId)) {
                $categoryId = current($categoryId);
            }

            if ($rewrite = $this->getCategoryRewrite((int)$categoryId)) {
                $identifier = $subject->removeCategorySuffix($rewrite);
            }
        } elseif ($allProductsIdentifier == $identifier && !empty($preparedSeoAliases)) {
            $identifier = '';
        }

        return [$identifier, $preparedSeoAliases];
    }

    /**
     * @param \Amasty\ShopbySeo\Helper\Url $subject
     * @param array $result
     * @return array
     */
    public function afterParseQuery($subject, $result)
    {
        $amshopbyParams = $subject->getParam(self::SHOPBY_EXTRA_PARAM);
        if ($amshopbyParams) {
            $amshopbyParams = is_array($amshopbyParams) ? $amshopbyParams : [$amshopbyParams];
            foreach ($amshopbyParams as $name => $value) {
                $subject->setParam($name, implode(',', $value));
            }
            $subject->setParam(self::SHOPBY_EXTRA_PARAM, null);
        }

        return $result;
    }

    /**
     * @param \Amasty\ShopbySeo\Helper\Url $subject
     * @param bool $result
     * @return bool
     */
    public function afterHasCategoryFilterParam($subject, $result)
    {
        return $result && !$this->shopbyCategoryHelper->isMultiselect();
    }

    private function getCategoryRewrite(int $categoryId): ?string
    {
        if (!isset($this->rewrites[$categoryId])) {
            try {
                $rewrite = $this->urlFinder->findOneByData([
                    UrlRewrite::ENTITY_ID => $categoryId,
                    UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => $this->storeManager->getStore()->getId(),
                    UrlRewrite::REDIRECT_TYPE => 0
                ]);

                $this->rewrites[$categoryId] = $rewrite ? $rewrite->getRequestPath() : null;
            } catch (NoSuchEntityException $e) {
                $this->rewrites[$categoryId] = null;
            }
        }

        return $this->rewrites[$categoryId];
    }
}
