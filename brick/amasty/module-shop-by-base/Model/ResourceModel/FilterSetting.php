<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\ResourceModel;

use Amasty\ShopbyBase\Helper\FilterSetting as FilterSettingHelper;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;

class FilterSetting extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * FilterSetting protected constructor
     */
    protected function _construct()
    {
        $this->_init(FilterSettingRepositoryInterface::TABLE, FilterSettingInterface::FILTER_SETTING_ID);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);
        $object->setFilterCode(FilterSettingHelper::ATTR_PREFIX . $object->getAttributeCode());

        return  $this;
    }
}
