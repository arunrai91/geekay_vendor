<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Setup\Patch\Schema;

use Amasty\RulesPro\Model\ResourceModel\RuleUsageLimit;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\SalesRule\Api\Data\RuleInterface;

class CreateLimitForeignKey implements SchemaPatchInterface
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

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        /** @var AdapterInterface $adapter */
        $adapter = $this->moduleDataSetup->getConnection();
        $usageLimitTableName = $this->moduleDataSetup->getTable(RuleUsageLimit::TABLE_NAME);
        $foreignKeys = $adapter->getForeignKeys($usageLimitTableName);
        
        if (!$foreignKeys) {
            $salesruleTableName = $this->moduleDataSetup->getTable('salesrule');
            $linkField = $this->metadata->getMetadata(RuleInterface::class)->getLinkField();
            $foreignKeyName = $adapter->getForeignKeyName(
                $usageLimitTableName,
                'salesrule_id',
                $salesruleTableName,
                $linkField
            );
            $adapter->addForeignKey(
                $foreignKeyName,
                $usageLimitTableName,
                'salesrule_id',
                $salesruleTableName,
                $linkField
            );
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
