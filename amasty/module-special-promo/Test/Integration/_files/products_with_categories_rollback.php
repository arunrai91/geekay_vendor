<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

$productSkus = ['simple1category', 'simple2category', 'simple3category', 'simple1-2category', '2simple1category'];

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

try {
    foreach ($productSkus as $sku) {
        $product = $productRepository->get($sku, false, null, true);
        $productRepository->delete($product);
    }
} catch (NoSuchEntityException $e) {
    //Product already removed
}
