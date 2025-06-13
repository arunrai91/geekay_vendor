<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */
/**
 * phpcs:ignoreFile
 * @codeCoverageIgnore
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productSkus = ['simple', 'simple2', 'simple3', 'simple4'];

/**
 * @var Magento\Catalog\Api\ProductRepositoryInterface $productRepository
 */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

try {
    foreach ($productSkus as $sku) {
        $product = $productRepository->get($sku, false, null, true);
        $productRepository->delete($product);
    }

} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    //Product already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
