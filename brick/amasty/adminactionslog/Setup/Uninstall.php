<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Setup;

use Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel\ActiveSession;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogDetail;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogEntry;
use Amasty\AdminActionsLog\Model\LoginAttempt\ResourceModel\LoginAttempt;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryDetail;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryEntry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->uninstallTables($setup)
            ->uninstallConfigData($setup)
            ->uninstallFlagData($setup)
            ->removeEmailTemplates($setup);
        $setup->endSetup();
    }

    private function uninstallTables(SchemaSetupInterface $setup): self
    {
        $tablesToDrop = [
            LoginAttempt::TABLE_NAME,
            VisitHistoryDetail::TABLE_NAME,
            VisitHistoryEntry::TABLE_NAME,
            ActiveSession::TABLE_NAME,
            LogDetail::TABLE_NAME,
            LogEntry::TABLE_NAME
        ];
        foreach ($tablesToDrop as $table) {
            $setup->getConnection()->dropTable(
                $setup->getTable($table)
            );
        }

        return $this;
    }

    private function uninstallConfigData(SchemaSetupInterface $setup): self
    {
        $setup->getConnection()->delete(
            $setup->getTable('core_config_data'),
            "`path` LIKE 'amaudit%'"
        );

        return $this;
    }

    private function uninstallFlagData(SchemaSetupInterface $setup): self
    {
        $setup->getConnection()->delete(
            $setup->getTable('flag'),
            '`flag_code` LIKE \'amasty_audit_%\''
        );

        return $this;
    }

    private function removeEmailTemplates(SchemaSetupInterface $setup): self
    {
        $setup->getConnection()->delete(
            $setup->getTable('email_template'),
            '`orig_template_code` LIKE \'amaudit_%\''
        );

        return $this;
    }
}
