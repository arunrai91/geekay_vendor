<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model\ResourceModel;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class BannerRule extends AbstractDb
{
    public const TABLE_NAME = 'amasty_banners_lite_rule';
    public const RULE_CATEGORIES_TABLE = 'amasty_banners_lite_rule_categories';
    public const RULE_PRODUCT_SKU_TABLE = 'amasty_banners_lite_rule_sku';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, BannerRuleInterface::ENTITY_ID);
    }

    protected function _afterLoad(AbstractModel $object): AbstractDb
    {
        $this->loadBannerRuleCategories($object);
        $this->loadBannerRuleSkus($object);

        return parent::_afterLoad($object);
    }

    public function loadBannerRuleCategories(AbstractModel $bannerRule): void
    {
        $select = $this->getConnection()->select()->from(['main_table' => $this->getMainTable()]);
        $select->joinInner(
            ['rule_categories' => $this->getTable(self::RULE_CATEGORIES_TABLE)],
            'main_table.entity_id = rule_categories.entity_id',
            ['banner_categories']
        );
        $select->reset(Select::COLUMNS)
            ->where('main_table.entity_id = ?', (int)$bannerRule->getEntityId())
            ->columns(['banner_product_categories' => 'rule_categories.banner_product_categories']);

        $bannerCategories = $select->getConnection()->fetchCol($select);
        $bannerRule->setData(BannerRuleInterface::BANNER_PRODUCT_CATEGORIES, implode(',', $bannerCategories));
    }

    public function loadBannerRuleSkus(AbstractModel $bannerRule): void
    {
        $select = $this->getConnection()->select()->from(['main_table' => $this->getMainTable()]);
        $select->joinInner(
            ['rule_product_sku' => $this->getTable(self::RULE_PRODUCT_SKU_TABLE)],
            'main_table.entity_id = rule_product_sku.entity_id',
            ['banner_product_sku']
        );
        $select->reset(Select::COLUMNS)
            ->where('main_table.entity_id = ?', (int)$bannerRule->getEntityId())
            ->columns(['banner_product_sku' => 'rule_product_sku.banner_product_sku']);

        $bannerProductSku = $select->getConnection()->fetchCol($select);
        $bannerRule->setData(BannerRuleInterface::BANNER_PRODUCT_SKU, implode(',', $bannerProductSku));
    }
}
