<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var CategoryRepository $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepository::class);

$categoryIds = [11, 22];
foreach ($categoryIds as $categoryId) {
    try {
        /** @var CategoryInterface $category */
        $category = $categoryRepository->get($categoryId);
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        continue;
    }

    foreach ($category->getProductCollection()->getItems() as $product) {
        $productRepository->delete($product);
    }

    if ($category->getId()) {
        $categoryRepository->delete($category);
    }
}



/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteriaBuilder->addFilter(ProductInterface::SKU, 'product_anchor_%', 'like');

/** @var ProductSearchResultsInterface $products */
$products = $productRepository->getList($searchCriteriaBuilder->create());
/** @var ProductInterface $product */
foreach ($products->getItems() as $product) {
    $productRepository->delete($product);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
