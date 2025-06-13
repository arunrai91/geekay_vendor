<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\ResourceModel\BannerRule as ResourceModel;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class ExtractCategoriesAndSkuData implements PatchInterface
{
    public const TEMPORARY_TABLE_NAME = 'amasty_banners_lite_categories_sku_tmp';

    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply(): ExtractCategoriesAndSkuData
    {
        if (!$this->isCanApply()) {
            return $this;
        }

        $connection = $this->schemaSetup->getConnection();
        $bannerRuleTable = $this->schemaSetup->getTable(ResourceModel::TABLE_NAME);
        $affectedColumns = [
            BannerRuleInterface::ENTITY_ID,
            BannerRuleInterface::BANNER_PRODUCT_CATEGORIES,
            BannerRuleInterface::BANNER_PRODUCT_SKU
        ];
        $select = $connection->select()->from(
            $bannerRuleTable,
            $affectedColumns
        );
        $this->createTemporaryTable();
        $connection->query(
            $connection->insertFromSelect(
                $select,
                $this->schemaSetup->getTable(self::TEMPORARY_TABLE_NAME),
                $affectedColumns
            )
        );

        return $this;
    }

    private function isCanApply(): bool
    {
        $connection = $this->schemaSetup->getConnection();
        $bannerRuleTable = $this->schemaSetup->getTable(ResourceModel::TABLE_NAME);

        return $connection->isTableExists($bannerRuleTable)
            && $connection->tableColumnExists($bannerRuleTable, BannerRuleInterface::BANNER_PRODUCT_CATEGORIES)
            && $connection->tableColumnExists($bannerRuleTable, BannerRuleInterface::BANNER_PRODUCT_SKU);
    }

    private function createTemporaryTable(): void
    {
        $this->schemaSetup->startSetup();
        $connection = $this->schemaSetup->getConnection();
        $table = $connection->newTable($this->schemaSetup->getTable(self::TEMPORARY_TABLE_NAME));
        $table->addColumn(
            BannerRuleInterface::ENTITY_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_UNSIGNED => true,
            ]
        );
        $table->addColumn(
            BannerRuleInterface::BANNER_PRODUCT_CATEGORIES,
            Table::TYPE_TEXT,
            null,
            [
                Table::OPTION_NULLABLE => true
            ]
        );
        $table->addColumn(
            BannerRuleInterface::BANNER_PRODUCT_SKU,
            Table::TYPE_TEXT,
            null,
            [
                Table::OPTION_NULLABLE => true
            ]
        );
        $connection->createTable($table);
        $this->schemaSetup->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
