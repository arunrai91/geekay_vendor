<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Setup\Patch\Data;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\ResourceModel\BannerRule as ResourceModel;
use Amasty\BannersLite\Setup\Patch\DeclarativeSchemaApplyBefore\ExtractCategoriesAndSkuData;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class FillCategoriesAndSkuTables implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): FillCategoriesAndSkuTables
    {
        if (!$this->isCanApply()) {
            return $this;
        }

        $this->moduleDataSetup->startSetup();

        $bannerCategories = $this->prepareDataToInsert(BannerRuleInterface::BANNER_PRODUCT_CATEGORIES);
        $bannerSkus = $this->prepareDataToInsert(BannerRuleInterface::BANNER_PRODUCT_SKU);

        if (!empty($bannerCategories)) {
            $this->insertData(ResourceModel::RULE_CATEGORIES_TABLE, $bannerCategories);
        }
        if (!empty($bannerSkus)) {
            $this->insertData(ResourceModel::RULE_PRODUCT_SKU_TABLE, $bannerSkus);
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    private function isCanApply(): bool
    {
        $connection = $this->moduleDataSetup->getConnection();
        $tmpTable = $this->moduleDataSetup->getTable(ExtractCategoriesAndSkuData::TEMPORARY_TABLE_NAME);

        return $connection->isTableExists($tmpTable);
    }

    private function prepareDataToInsert(string $column): array
    {
        $connection = $this->moduleDataSetup->getConnection();
        $tableName = $this->moduleDataSetup->getTable(ExtractCategoriesAndSkuData::TEMPORARY_TABLE_NAME);
        $select = $connection->select()->from(
            $tableName,
            [BannerRuleInterface::ENTITY_ID, $column]
        );

        $dataToInsert = [];
        foreach (array_filter($connection->fetchPairs($select)) as $entityId => $data) {
            if (isset($data)) {
                $data = array_unique(explode(',', $data));
                foreach ($data as $row) {
                    $dataToInsert[] = [
                        BannerRuleInterface::ENTITY_ID => $entityId,
                        $column => $row
                    ];
                }
            }
        }

        return $dataToInsert;
    }

    private function insertData(string $table, array $data): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->insertOnDuplicate($this->moduleDataSetup->getTable($table), $data);
    }

    public static function getDependencies(): array
    {
        return [
            ExtractCategoriesAndSkuData::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
