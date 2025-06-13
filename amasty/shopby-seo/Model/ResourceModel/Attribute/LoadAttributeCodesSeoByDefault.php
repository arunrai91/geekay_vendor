<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\ResourceModel\Attribute;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Amasty\ShopbySeo\Model\Source\GenerateSeoUrl;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\DB\Select;

class LoadAttributeCodesSeoByDefault
{
    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    public function __construct(AttributeCollectionFactory $attributeCollectionFactory)
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @return string[]
     */
    public function execute(): array
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter('frontend_input', ['neq' => 'price']);
        $attributeCollection->addFieldToFilter('additional_table.is_filterable', ['gt' => 0]);

        $attributeCollection->getSelect()->joinLeft(
            ['filter_setting' => $attributeCollection->getTable(FilterSettingRepositoryInterface::TABLE)],
            sprintf('additional_table.attribute_id = filter_setting.%s', FilterSettingInterface::ATTRIBUTE_ID),
            []
        );
        $attributeCollection->addFieldToFilter(
            [FilterSettingInterface::IS_SEO_SIGNIFICANT, FilterSettingInterface::IS_SEO_SIGNIFICANT],
            [
                ['neq' => GenerateSeoUrl::NO],
                ['null' => true]
            ]
        );

        $attributeCollection->getSelect()->reset(Select::COLUMNS);
        $attributeCollection->addFieldToSelect('attribute_code');

        return $attributeCollection->getConnection()->fetchCol($attributeCollection->getSelect()) ?: [];
    }
}
