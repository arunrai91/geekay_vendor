<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Setup\Patch\Data;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Helper\FilterSetting;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting;
use Magento\Framework\DB\Sql\ColumnValueExpression;
use Magento\Framework\DB\Sql\ColumnValueExpressionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update Filter Options attribute code values by filter code.
 */
class FillAttributeCodeOptions implements DataPatchInterface
{
    /**
     * @var OptionSetting
     */
    private $optionSettingResource;

    /**
     * @var ColumnValueExpressionFactory
     */
    private $columnValueExpressionFactory;

    public function __construct(
        OptionSetting $optionSettingResource,
        ColumnValueExpressionFactory $columnValueExpressionFactory
    ) {
        $this->optionSettingResource = $optionSettingResource;
        $this->columnValueExpressionFactory = $columnValueExpressionFactory;
    }

    /**
     * @return $this
     */
    public function apply()
    {
        $connection = $this->optionSettingResource->getConnection();

        $connection->update(
            $this->optionSettingResource->getMainTable(),
            [OptionSettingInterface::ATTRIBUTE_CODE => $this->getExpression()]
        );

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get attribute code expression.
     */
    private function getExpression(): ColumnValueExpression
    {
        $connection = $this->optionSettingResource->getConnection();

        $condition = sprintf(
            'IF (LEFT(%s, 5) = %s, SUBSTR(%1$s, 6), %1$s)',
            OptionSettingInterface::FILTER_CODE,
            $connection->quote(FilterSetting::ATTR_PREFIX)
        );

        return $this->columnValueExpressionFactory->create(['expression' => $condition]);
    }
}
