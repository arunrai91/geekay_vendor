<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Setup\Patch\Schema;

use Amasty\Rules\Api\Data\RuleInterface;
use Amasty\Rules\Model\ResourceModel\Rule;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;

/**
 * Update or add a foreign key for salesrule table according to primary key
 */
class UpdateForeignKey implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var MetadataPool
     */
    private $metadata;

    public function __construct(
        MetadataPool $metadata,
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
        $this->metadata = $metadata;
    }

    /**
     * @return void
     * @throws \Zend_Db_Select_Exception
     */
    public function apply(): void
    {
        /** @var AdapterInterface $adapter */
        $adapter = $this->setup->getConnection();
        $amruleTableName = $this->setup->getTable(Rule::TABLE_NAME);
        $salesruleTableName = $this->setup->getTable('salesrule');
        $foreignKeys = $adapter->getForeignKeys($amruleTableName);
        $linkField = $this->metadata->getMetadata(SalesRuleInterface::class)->getLinkField();
        $fkName = $adapter->getForeignKeyName(
            $amruleTableName,
            RuleInterface::KEY_SALESRULE_ID,
            $salesruleTableName,
            $linkField
        );
        
        if (empty($foreignKeys)) {
            $adapter->addForeignKey(
                $fkName,
                $amruleTableName,
                RuleInterface::KEY_SALESRULE_ID,
                $salesruleTableName,
                $linkField
            );

            return;
        }

        foreach ($foreignKeys as $key) {
            if ($key['COLUMN_NAME'] == RuleInterface::KEY_SALESRULE_ID && $key['REF_COLUMN_NAME'] != $linkField) {
                $this->setRowIdInsteadRuleId($adapter, $amruleTableName, $salesruleTableName);
                $adapter->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
                $adapter->addForeignKey(
                    $fkName,
                    $amruleTableName,
                    RuleInterface::KEY_SALESRULE_ID,
                    $salesruleTableName,
                    $linkField
                );
            }
        }
    }

    /**
     * @param AdapterInterface $adapter
     * @param string $amruleTableName
     * @param string $salesruleTableName
     * @return void
     * @throws \Zend_Db_Select_Exception
     */
    private function setRowIdInsteadRuleId(
        AdapterInterface $adapter,
        string $amruleTableName,
        string $salesruleTableName
    ): void {
        $select = $adapter->select()
            ->from(
                $amruleTableName,
                [
                    RuleInterface::KEY_EACHM,
                    RuleInterface::KEY_PRICESELECTOR,
                    RuleInterface::KEY_PROMO_CATS,
                    RuleInterface::KEY_PROMO_SKUS,
                    RuleInterface::KEY_NQTY,
                    RuleInterface::KEY_SKIP_RULE,
                    RuleInterface::KEY_MAX_DISCOUNT,
                    RuleInterface::KEY_APPLY_DISCOUNT_TO,
                    'use_for'
                ]
            )->joinInner(
                ['salesrule' => $salesruleTableName],
                'salesrule.rule_id = ' . $amruleTableName . '.salesrule_id',
                ['salesrule_id' => 'salesrule.row_id']
            )->setPart('disable_staging_preview', true);

        $amRules = $adapter->fetchAll($select);

        $adapter->truncateTable($amruleTableName);

        foreach ($amRules as $rule) {
            $adapter->insertMultiple($amruleTableName, $rule);
        }
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
