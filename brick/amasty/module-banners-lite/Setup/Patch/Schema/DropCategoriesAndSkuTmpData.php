<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Setup\Patch\Schema;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\ResourceModel\BannerRule as ResourceModel;
use Amasty\BannersLite\Setup\Patch\Data\FillCategoriesAndSkuTables;
use Amasty\BannersLite\Setup\Patch\DeclarativeSchemaApplyBefore\ExtractCategoriesAndSkuData;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class DropCategoriesAndSkuTmpData implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    public function __construct(
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
    }

    public function apply(): void
    {
        $this->dropTmpData();
    }

    private function dropTmpData(): void
    {
        $connection = $this->setup->getConnection();
        $bannerRuleTable = $this->setup->getTable(ResourceModel::TABLE_NAME);
        $tmpTable =  $this->setup->getTable(ExtractCategoriesAndSkuData::TEMPORARY_TABLE_NAME);

        if ($connection->isTableExists($tmpTable)) {
            $connection->dropTable($tmpTable);
        }
        if ($connection->tableColumnExists($bannerRuleTable, BannerRuleInterface::BANNER_PRODUCT_CATEGORIES)) {
            $connection->dropColumn($bannerRuleTable, BannerRuleInterface::BANNER_PRODUCT_CATEGORIES);
        }
        if ($connection->tableColumnExists($bannerRuleTable, BannerRuleInterface::BANNER_PRODUCT_SKU)) {
            $connection->dropColumn($bannerRuleTable, BannerRuleInterface::BANNER_PRODUCT_SKU);
        }
    }

    public static function getDependencies(): array
    {
        return [
            ExtractCategoriesAndSkuData::class,
            FillCategoriesAndSkuTables::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
