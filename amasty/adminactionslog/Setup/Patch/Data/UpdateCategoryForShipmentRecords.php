<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Setup\Patch\Data;

use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Sales\Shipment;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry as LogEntryModel;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogEntry as LogEntryResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateCategoryForShipmentRecords implements DataPatchInterface
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

    public function apply(): self
    {
        $tableName = $this->moduleDataSetup->getTable(LogEntryResource::TABLE_NAME);
        $this->moduleDataSetup->getConnection()->update(
            $tableName,
            [LogEntryModel::CATEGORY => Shipment::CATEGORY],
            [LogEntryModel::CATEGORY . ' = ?' => 'sales/order_shipment/view']
        );

        return $this;
    }
}
