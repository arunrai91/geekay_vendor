<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Setup\Patch\DeclarativeSchemaApplyBefore;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class ModifyOptionValueColumn implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * @return ModifyOptionValueColumn
     */
    public function apply()
    {
        if (!$this->isNeedApply()) {
            return $this;
        }

        $this->schemaSetup->getConnection()->modifyColumn(
            $this->schemaSetup->getTable('amasty_amshopby_option_setting'),
            'value',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => false,
                'unsigned' => true,
                'comment' =>'Attribute Option ID',
            ]
        );

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    private function isNeedApply(): bool
    {
        return $this->schemaSetup->tableExists('amasty_amshopby_option_setting')
            && $this->schemaSetup->getConnection()->tableColumnExists(
                $this->schemaSetup->getTable('amasty_amshopby_option_setting'),
                'value'
            );
    }
}
