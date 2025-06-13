<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Setup\Patch\Data;

use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogEntry as LogEntryResource;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateLogData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var LogEntryTypes
     */
    private $logEntryTypes;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceInterface $moduleResource,
        LogEntryTypes $logEntryTypes
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->moduleResource = $moduleResource;
        $this->logEntryTypes = $logEntryTypes;
    }

    public function apply(): MigrateLogData
    {
        $setupDataVersion = (string)$this->moduleResource->getDataVersion('Amasty_AdminActionsLog');
        if ($setupDataVersion && version_compare($setupDataVersion, '2.0.0', '<')) {
            $this->migrateLogTypes();
            $this->migrateCategories();
            $this->migrateExportTypes();
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [
            MigrateLogEntryData::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }

    private function migrateExportTypes(): void
    {
        $tableName = $this->moduleDataSetup->getTable(LogEntryResource::TABLE_NAME);
        $oldExportTypes = ['exportCsv', 'exportXml'];

        foreach ($oldExportTypes as $oldExportType) {
            $this->moduleDataSetup->getConnection()->update(
                $tableName,
                [LogEntry::TYPE => 'export'],
                [LogEntry::TYPE . ' = ?' => $oldExportType]
            );
        }
    }

    private function migrateCategories(): void
    {
        $tableName = $this->moduleDataSetup->getTable(LogEntryResource::TABLE_NAME);
        $categoriesMapping = [
            'catalog/product' => [
                LogEntry::CATEGORY => 'catalog/product/edit',
                LogEntry::CATEGORY_NAME => __('Catalog Product'),
                LogEntry::PARAMETER_NAME => 'id'
            ],
            'customer' => [
                LogEntry::CATEGORY => 'customer/index/edit',
                LogEntry::CATEGORY_NAME => __('Customer'),
                LogEntry::PARAMETER_NAME => 'id'
            ],
            'customer/index' => [
                LogEntry::CATEGORY => 'customer/index/edit',
                LogEntry::CATEGORY_NAME => __('Customer'),
                LogEntry::PARAMETER_NAME => 'id'
            ],
            'customer/group' => [
                LogEntry::CATEGORY => 'customer/group/edit',
                LogEntry::CATEGORY_NAME => __('Customer Group'),
                LogEntry::PARAMETER_NAME => 'id'
            ],
            'catalog/product_attribute' => [
                LogEntry::CATEGORY => 'catalog/product_attribute/edit',
                LogEntry::CATEGORY_NAME => __('Product Attribute'),
                LogEntry::PARAMETER_NAME => 'attribute_id'
            ],
            'sales/order_create' => [
                LogEntry::CATEGORY => 'sales/order/view',
                LogEntry::CATEGORY_NAME => __('Order'),
                LogEntry::PARAMETER_NAME => 'order_id'
            ],
            'sales/order' => [
                LogEntry::CATEGORY => 'sales/order/view',
                LogEntry::CATEGORY_NAME => __('Order'),
                LogEntry::PARAMETER_NAME => 'order_id'
            ],
            'admin/order_shipment' => [
                LogEntry::CATEGORY => 'sales/shipment/view',
                LogEntry::CATEGORY_NAME => __('Shipment'),
                LogEntry::PARAMETER_NAME => 'shipment_id'
            ],
            'admin/order_creditmemo' => [
                LogEntry::CATEGORY => 'sales/creditmemo/view',
                LogEntry::CATEGORY_NAME => __('Credit Memo'),
                LogEntry::PARAMETER_NAME => 'creditmemo_id'
            ],
            'admin/order_invoice' => [
                LogEntry::CATEGORY => 'sales/invoice/view',
                LogEntry::CATEGORY_NAME => __('Invoice'),
                LogEntry::PARAMETER_NAME => 'invoice_id'
            ],
            'catalog_rule/promo_catalog' => [
                LogEntry::CATEGORY => 'catalog_rule/promo_catalog/edit',
                LogEntry::CATEGORY_NAME => __('Catalog Price Rule'),
                LogEntry::PARAMETER_NAME => 'id'
            ]
        ];

        foreach ($categoriesMapping as $origCategory => $dataToUpdate) {
            $this->moduleDataSetup->getConnection()->update(
                $tableName,
                $dataToUpdate,
                [LogEntry::CATEGORY . ' = ?' => $origCategory]
            );
        }
    }

    private function migrateLogTypes(): void
    {
        $tableName = $this->moduleDataSetup->getTable(LogEntryResource::TABLE_NAME);

        foreach ($this->logEntryTypes->toArray() as $typeKey => $typeLabel) {
            $this->moduleDataSetup->getConnection()->update(
                $tableName,
                [LogEntry::TYPE => $typeKey],
                [LogEntry::TYPE . ' = ?' => (string)$typeLabel]
            );
        }
    }
}
