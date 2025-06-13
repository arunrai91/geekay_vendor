<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\FilterSetting;
use Amasty\ShopbySeo\Model\Source\GenerateSeoUrl;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var $attribute Attribute */
$attribute = $objectManager->create(Attribute::class);
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(ProductAttributeRepositoryInterface::class);
/** @var $installer CategorySetup */
$installer = $objectManager->create(CategorySetup::class);
/** @var FilterSetting $installer */
$filterSettings = $objectManager->create(FilterSettingInterface::class);
$entityTypeId = $installer->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);

$attributeCode = 'dropdown_attribute';
if (!$attribute->loadByCode($entityTypeId, $attributeCode)->getId()) {
    $attribute->setData(
        [
            'attribute_code'                => $attributeCode,
            'entity_type_id'                => $entityTypeId,
            'is_global'                     => 0,
            'is_user_defined'               => 1,
            'frontend_input'                => 'select',
            'is_unique'                     => 0,
            'is_required'                   => 0,
            'is_searchable'                 => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable'                 => 0,
            'is_filterable'                 => 1,
            'is_filterable_in_search'       => 0,
            'is_used_for_promo_rules'       => 0,
            'is_html_allowed_on_front'      => 1,
            'is_visible_on_front'           => 1,
            'used_in_product_listing'       => 1,
            'used_for_sort_by'              => 0,
            'frontend_label'                => ['Drop-Down Attribute'],
            'backend_type'                  => 'int',
            'option'                        => [
                'value' => [
                    'option_1' => ['Option 1'],
                    'option_2' => ['Option 2'],
                    'option_3' => ['Option 3'],
                    $attributeCode => [$attributeCode],
                    'someattr' => ['someattr'],
                    'sameoption' => ['sameoption'],
                ],
                'order' => [
                    'option_1' => 1,
                    'option_2' => 2,
                    'option_3' => 3,
                    $attributeCode => 4,
                    'someattr' => 5,
                    'sameoption' => 6,
                ],
            ]
        ]
    );
    $filterSettings->setData([
        FilterSettingInterface::ATTRIBUTE_CODE => $attributeCode,
        FilterSettingInterface::DISPLAY_MODE => DisplayMode::MODE_DEFAULT,
        FilterSettingInterface::IS_SEO_SIGNIFICANT => GenerateSeoUrl::YES,
        FilterSettingInterface::IS_MULTISELECT => 1
    ]);
    $attribute->getExtensionAttributes()->setFilterSetting($filterSettings);
    $attributeRepository->save($attribute);
    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup(
        ProductAttributeInterface::ENTITY_TYPE_CODE,
        'Default',
        'Attributes',
        $attribute->getId()
    );
}
