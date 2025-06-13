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
 * Fix refactoring bug.
 *
 * Remove duplicated options without attr prefix.
 */
class DeleteBrandDuplicates implements DataPatchInterface
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

        $connection->delete(
            $this->optionSettingResource->getMainTable(),
            $this->getExpression()
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
     * Get attribute code SQL expression.
     */
    private function getExpression(): ColumnValueExpression
    {
        $connection = $this->optionSettingResource->getConnection();

        $condition = sprintf(
            'LEFT(%s, 5) != %s',
            OptionSettingInterface::FILTER_CODE,
            $connection->quote(FilterSetting::ATTR_PREFIX)
        );

        return $this->columnValueExpressionFactory->create(['expression' => $condition]);
    }
}
