<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\ResourceModel\FilterSetting;

/**
 * @method \Amasty\ShopbyBase\Model\FilterSetting[] getItems
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection protected constructor
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\ShopbyBase\Model\FilterSetting::class,
            \Amasty\ShopbyBase\Model\ResourceModel\FilterSetting::class
        );
    }

    public function addIsFilterableFilter(): self
    {
        $this->getSelect()->joinLeft(
            ['c_ea' => $this->getTable('catalog_eav_attribute')],
            'main_table.attribute_id = c_ea.attribute_id',
            []
        )
        ->where(
            'c_ea.is_filterable = 1 ' .
            'OR c_ea.is_filterable = 2 ' .
            "OR main_table.attribute_code = 'category_ids'"
        );

        return $this;
    }
}
