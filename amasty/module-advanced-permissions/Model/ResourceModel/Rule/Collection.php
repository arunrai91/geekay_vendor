<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\ResourceModel\Rule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method \Amasty\Rolepermissions\Model\ResourceModel\Rule getResource()
 * @method \Amasty\Rolepermissions\Model\Rule[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\Rolepermissions\Model\Rule::class,
            \Amasty\Rolepermissions\Model\ResourceModel\Rule::class
        );
    }

    /**
     * @param array|int $categoryIds
     *
     * @return $this
     */
    public function addCategoriesFilter($categoryIds)
    {
        $this->getResource()->addRelationFilter($this->getSelect(), $categoryIds, 'categories');

        return $this;
    }
}
