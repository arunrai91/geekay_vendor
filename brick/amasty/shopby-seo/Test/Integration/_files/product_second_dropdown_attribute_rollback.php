<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

use Amasty\ShopbyBase\Model\FilterSettingRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
/** @var FilterSettingRepository $filterRepository */
$filterRepository = $objectManager->get(FilterSettingRepository::class);

try {
    $attributeRepository->deleteById('someattr');
    $filterRepository->deleteByAttributeCode('someattr');
} catch (NoSuchEntityException $e) {
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
