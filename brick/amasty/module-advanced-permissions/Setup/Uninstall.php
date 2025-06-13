<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->uninstallTables($setup);
    }

    private function uninstallTables(SchemaSetupInterface $setup): self
    {
        $tablesToDrop = [
            'amasty_amrolepermissions_rule_product',
            'amasty_amrolepermissions_rule_category',
            'amasty_amrolepermissions_rule_website',
            'amasty_amrolepermissions_rule_storeview',
            'amasty_amrolepermissions_rule_attribute',
            'amasty_amrolepermissions_rule_role',
            'amasty_amrolepermissions_rule'
        ];
        foreach ($tablesToDrop as $table) {
            $setup->getConnection()->dropTable(
                $setup->getTable($table)
            );
        }

        return $this;
    }
}
