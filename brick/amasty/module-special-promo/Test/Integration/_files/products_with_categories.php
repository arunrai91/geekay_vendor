<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$category1 = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category1->isObjectNew(true);
$category1->setId(1)
    ->setName('Category 1')
    ->setPath('1/2')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$category2 = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category2->isObjectNew(true);
$category2->setId(2)
    ->setName('Category 2')
    ->setPath('1/2')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2)
    ->save();

$category3 = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category3->isObjectNew(true);
$category3->setId(3)
    ->setName('Category 3')
    ->setPath('1/2')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(3)
    ->save();

$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
/** @var $product Product */
$product = $objectManager->create(Product::class);
$product
    ->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple1category')
    ->setPrice(10)
    ->setCategoryIds([1])
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setUrlKey('simple-product')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22);
$productRepository->save($product);

$product2 = $objectManager->create(Product::class);
$product2
    ->setTypeId('simple')
    ->setId(2)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setSku('simple2category')
    ->setName('Simple Product2')
    ->setPrice(20)
    ->setCategoryIds([2])
    ->setMetaTitle('meta title2')
    ->setMetaKeyword('meta keyword2')
    ->setMetaDescription('meta description2')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setUrlKey('simple-product2');
$productRepository->save($product2);

$product3 = $objectManager->create(Product::class);
$product3
    ->setTypeId('simple')
    ->setId(3)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setSku('simple3category')
    ->setName('Simple Product3')
    ->setPrice(30)
    ->setCategoryIds([3])
    ->setMetaTitle('meta title3')
    ->setUrlKey('simple-product3')
    ->setMetaKeyword('meta keyword3')
    ->setMetaDescription('meta description3')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setUrlKey('simple-product3');
$productRepository->save($product3);

$product4 = $objectManager->create(Product::class);
$product4
    ->setTypeId('simple')
    ->setId(4)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setSku('simple1-2category')
    ->setName('Simple Product4')
    ->setPrice(40)
    ->setCategoryIds([1,2])
    ->setMetaTitle('meta title4')
    ->setUrlKey('simple-product4')
    ->setMetaKeyword('meta keyword4')
    ->setMetaDescription('meta description4')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setUrlKey('simple-product4');
$productRepository->save($product4);

$product5 = $objectManager->create(Product::class);
$product5
    ->setTypeId('simple')
    ->setId(5)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setSku('2simple1category')
    ->setName('Simple Product5')
    ->setPrice(50)
    ->setCategoryIds([1])
    ->setMetaTitle('meta title5')
    ->setMetaKeyword('meta keyword5')
    ->setMetaDescription('meta description5')
    ->setUrlKey('simple-product5')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setUrlKey('simple-product5');
$productRepository->save($product5);
