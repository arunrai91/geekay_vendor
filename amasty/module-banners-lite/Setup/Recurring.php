<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Setup;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\ResourceModel\Banner;
use Amasty\BannersLite\Model\ResourceModel\BannerRule;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\SalesRule\Api\Data\RuleInterface;

class Recurring implements InstallSchemaInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var MetadataPool
     */
    private $metadata;

    public function __construct(
        MetadataPool $metadata,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->metadata = $metadata;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->createSalesruleFk();
    }

    private function createSalesruleFk()
    {
        $this->addFkToTable(Banner::TABLE_NAME, BannerInterface::SALESRULE_ID);
        $this->addFkToTable(BannerRule::TABLE_NAME, BannerRuleInterface::SALESRULE_ID);
    }

    private function addFkToTable(string $tableName, string $fkColumnIndex)
    {
        $needToAddFK = true;
        /** @var AdapterInterface $adapter */
        $adapter = $this->moduleDataSetup->getConnection();
        $amBannerTableName = $this->moduleDataSetup->getTable($tableName);
        $salesruleTableName = $this->moduleDataSetup->getTable('salesrule');
        $foreignKeys = $adapter->getForeignKeys($amBannerTableName);
        $linkField = $this->metadata->getMetadata(RuleInterface::class)->getLinkField();
        if ($foreignKeys) {
            foreach ($foreignKeys as $key) {
                if ($key['COLUMN_NAME'] == $fkColumnIndex) {
                    if ($key['REF_COLUMN_NAME'] != $linkField) {
                        $adapter->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
                    } else {
                        $needToAddFK = false;
                    }
                }
            }
        }

        if ($needToAddFK) {
            $adapter->addForeignKey(
                $adapter->getForeignKeyName(
                    $amBannerTableName,
                    $fkColumnIndex,
                    $salesruleTableName,
                    $linkField
                ),
                $amBannerTableName,
                $fkColumnIndex,
                $salesruleTableName,
                $linkField
            );
        }
    }
}
