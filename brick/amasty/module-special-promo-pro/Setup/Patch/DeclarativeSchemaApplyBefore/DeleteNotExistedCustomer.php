<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\RulesPro\Api\Data\QueueInterface;
use Amasty\RulesPro\Model\ResourceModel\Queue;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DeleteNotExistedCustomer implements DataPatchInterface
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

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): DeleteNotExistedCustomer
    {
        $table = $this->moduleDataSetup->getTable(Queue::MAIN_TABLE);
        if ($this->moduleDataSetup->getConnection()->isTableExists($table)
            && $this->moduleDataSetup->getConnection()->tableColumnExists(
                $table,
                QueueInterface::CUSTOMER_ID
            )
        ) {
            $this->deleteCustomer();
        }

        return $this;
    }

    private function deleteCustomer(): void
    {
        $amrulesCacheQueueTable = $this->moduleDataSetup->getTable(Queue::MAIN_TABLE);
        $customerTable = $this->moduleDataSetup->getTable('customer_entity');

        $existedCustomerSelect = $this->moduleDataSetup->getConnection()->select()
            ->from(
                ['cqt' => $amrulesCacheQueueTable],
                [QueueInterface::CUSTOMER_ID]
            )->joinLeft(
                ['ce' => $customerTable],
                'ce.entity_id = cqt.' . QueueInterface::CUSTOMER_ID,
                []
            )->where(
                'ce.entity_id = cqt.' . QueueInterface::CUSTOMER_ID
            );
        $customerIds = $this->moduleDataSetup->getConnection()->fetchCol($existedCustomerSelect);

        $notExistedCustomerSelect = $this->moduleDataSetup->getConnection()->select()
            ->from(
                ['main_table' => $amrulesCacheQueueTable],
                []
            )->where(
                'main_table.' . QueueInterface::CUSTOMER_ID . ' NOT IN (?)',
                $customerIds
            );

        $delete = $this->moduleDataSetup->getConnection()->deleteFromSelect($notExistedCustomerSelect, 'main_table');
        $this->moduleDataSetup->getConnection()->query($delete);
    }
}
