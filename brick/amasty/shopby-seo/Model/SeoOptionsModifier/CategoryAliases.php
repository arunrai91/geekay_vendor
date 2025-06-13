<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\SeoOptionsModifier;

use Amasty\ShopbySeo\Helper\Url as UrlHelper;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

class CategoryAliases implements SeoModifierInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UniqueBuilder
     */
    private $uniqueBuilder;

    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        UniqueBuilder $uniqueBuilder
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->uniqueBuilder = $uniqueBuilder;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function modify(array &$optionsSeoData, int $storeId, array &$attributeIds = []): void
    {
        foreach ($this->getCategories($storeId) as $categoryId => $categoryName) {
            $alias = $this->uniqueBuilder->execute(
                $categoryName,
                UrlHelper::CATEGORY_FILTER_PARAM . '-' . $categoryId,
                true
            );
            $optionsSeoData[$storeId][UrlHelper::CATEGORY_FILTER_PARAM][$categoryId] = $alias;
        }
    }

    /**
     * @return array ['category_id' => 'category_name', ...]
     */
    private function getCategories(int $storeId): array
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addFieldToFilter(
            'path',
            ['like' => '1/' . $this->getRootCategoryId($storeId) . '/%']
        ); //load only from store root
        $categoryCollection->addIsActiveFilter();

        $categoryCollection->getSelect()->reset(Select::COLUMNS);
        $categoryCollection->getSelect()->columns('entity_id');

        $categoryCollection->addAttributeToSelect('url_key', 'inner');

        return $categoryCollection->getResource()->getConnection()->fetchPairs(
            $categoryCollection->getSelect()
        );
    }

    private function getRootCategoryId(int $storeId): int
    {
        return (int) $this->storeManager->getStore($storeId)->getRootCategoryId();
    }
}
