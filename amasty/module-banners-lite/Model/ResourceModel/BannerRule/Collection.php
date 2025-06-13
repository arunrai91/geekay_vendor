<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model\ResourceModel\BannerRule;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\BannerRule;
use Amasty\BannersLite\Model\ResourceModel\BannerRule as ResourceModel;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(BannerRule::class, ResourceModel::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param string $productSku
     * @param array $productCats
     *
     * @return array
     */
    public function getValidBannerRuleIds($productSku, $productCats)
    {
        $this->getSelect()->where($this->prepareSqlCondition($productSku, $productCats));

        return $this->getRuleIds();
    }

    /**
     * @param string $productSku
     * @param array $productCats
     *
     * @return string
     */
    private function prepareSqlCondition($productSku, $productCats)
    {
        /* show_banner_for = '0' */
        $sql = $this->getConnection()->prepareSqlCondition(
            BannerRuleInterface::SHOW_BANNER_FOR,
            BannerRuleInterface::ALL_PRODUCTS
        );

        if ($productSku) {
            $skuCondition = $this->getConnection()
                ->quoteInto('rule_sku.banner_product_sku = ?', strtolower($productSku));
            $sql .= " OR (main_table.show_banner_for = '" . BannerRuleInterface::PRODUCT_SKU
                . "' AND " . $skuCondition . ")";
        }

        if (!empty($productCats)) {
            $catConditions = [];
            foreach ($productCats as $category) {
                $catConditions[] = $this->getConnection()
                    ->quoteInto('rule_categories.banner_product_categories = ?', $category);
            }
            $catSql = implode(' OR ', $catConditions);
            $sql .= " OR (main_table.show_banner_for = '" . BannerRuleInterface::PRODUCT_CATEGORY
                . "' AND (" . $catSql . "))";
        }

        return $sql;
    }

    /**
     * @return array
     */
    private function getRuleIds()
    {
        $select = clone $this->getSelect();

        $select->reset(Select::ORDER);
        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);

        $select->joinLeft(
            ['rule_categories' => $this->getTable(ResourceModel::RULE_CATEGORIES_TABLE)],
            'main_table.entity_id = rule_categories.entity_id',
            ['banner_product_categories']
        )->joinLeft(
            ['rule_sku' => $this->getTable(ResourceModel::RULE_PRODUCT_SKU_TABLE)],
            'main_table.entity_id = rule_sku.entity_id',
            ['banner_product_sku']
        )->group('main_table.entity_id');

        $select->reset(Select::COLUMNS);
        $select->columns(BannerRuleInterface::SALESRULE_ID, 'main_table');

        return $this->getConnection()->fetchAll($select, $this->_bindParams);
    }
}
