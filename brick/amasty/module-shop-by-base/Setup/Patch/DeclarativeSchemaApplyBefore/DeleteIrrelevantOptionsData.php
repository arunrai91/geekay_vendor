<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Clean up irrelevant data before adding foreign keys
 */
class DeleteIrrelevantOptionsData implements DataPatchInterface
{
    /**
     * @var OptionSetting
     */
    private $optionsResource;

    public function __construct(
        OptionSetting $optionsResource
    ) {
        $this->optionsResource = $optionsResource;
    }

    /**
     * @return $this
     */
    public function apply()
    {
        if ($this->optionsResource->getConnection()->isTableExists($this->optionsResource->getMainTable())) {
            $this->clearOptions();
        }

        return $this;
    }

    private function clearOptions(): void
    {
        $connection = $this->optionsResource->getConnection();

        $select = $connection->select()->from(
            $this->optionsResource->getTable('eav_attribute_option'),
            ['option_id']
        );
        $connection->delete(
            $this->optionsResource->getMainTable(),
            $connection->quoteInto('value NOT IN(?)', $select)
        );
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
