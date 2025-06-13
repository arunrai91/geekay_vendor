<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\ResourceModel;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Amasty\ShopbyBase\Helper\FilterSetting as FilterSettingHelper;
use Amasty\ShopbyBase\Model\OptionSetting as OptionSettingModel;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;

class OptionSetting extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * OptionSetting protected constructor
     */
    protected function _construct()
    {
        $this->_init(OptionSettingRepositoryInterface::TABLE, OptionSettingInterface::OPTION_SETTING_ID);
    }

    /**
     * @param AbstractModel|OptionSettingModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        parent::_beforeSave($object);
        //backward compatibility
        $object->setFilterCode(FilterSettingHelper::ATTR_PREFIX . $object->getAttributeCode());

        return  $this;
    }

    /**
     * Allow saving empty string and null values for URL alias field.
     * The URL Alias field have logic for null and for empty value.
     *
     * @param OptionSettingModel $object
     * @param string $table
     * @return array
     * @throws LocalizedException
     */
    protected function _prepareDataForTable(DataObject $object, $table)
    {
        $data = parent::_prepareDataForTable($object, $table);

        foreach ($data as $field => $value) {
            if ($value === null && $object->getData($field) === '') {
                $data[$field] = ''; // provide possibility for override custom store with empty value
            }
        }

        return $data;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getAllFeaturedOptionsArray($storeId)
    {
        $options = [];
        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            ['value', 'store_id', OptionSettingInterface::ATTRIBUTE_CODE]
        )->where(
            'store_id IN(?)',
            [Store::DEFAULT_STORE_ID, $storeId]
        )->where('is_featured = 1');

        $result = $this->getConnection()->fetchAll($select);
        foreach ($result as $option) {
            $options[$option[OptionSettingInterface::ATTRIBUTE_CODE]][$option['value']][$option['store_id']] = true;
        }

        return $options;
    }

    /**
     * @param int $storeId
     * @param string[]|null $seoAttributeCodes
     * @return array [['attribute_code' => '', 'value' => '', 'url_alias' => ''], ...]
     */
    public function getHardcodedAliases(int $storeId = Store::DEFAULT_STORE_ID, ?array $seoAttributeCodes = null): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['main_table' => $this->getMainTable()],
            [OptionSettingInterface::ATTRIBUTE_CODE, OptionSettingInterface::VALUE]
        );
        $select->where('`main_table`.`store_id` = ?', Store::DEFAULT_STORE_ID);
        if ($seoAttributeCodes) {
            $select->where(
                sprintf('main_table.%s IN (?)', OptionSettingInterface::ATTRIBUTE_CODE),
                $seoAttributeCodes
            );
        }
        if ($storeId === Store::DEFAULT_STORE_ID) {
            $select->columns(OptionSettingInterface::URL_ALIAS);
            $select->where(OptionSettingInterface::URL_ALIAS . ' <> ?', '');
            $select->where(OptionSettingInterface::URL_ALIAS . ' IS NOT NULL');
        } else {
            $urlAlias = $connection->getIfNullSql('`store_value`.`url_alias`', '`main_table`.`url_alias`');
            $select->joinLeft(
                ['store_value' => $this->getMainTable()],
                '`store_value`.`value` = `main_table`.`value` AND ' .
                $connection->quoteInto('`store_value`.`store_id` = ?', $storeId),
                ['url_alias' => $urlAlias]
            );

            $select->where($urlAlias . ' <> ?', '');
            $select->where($urlAlias . ' IS NOT NULL');
        }

        return $select->getConnection()->fetchAll($select);
    }
}
