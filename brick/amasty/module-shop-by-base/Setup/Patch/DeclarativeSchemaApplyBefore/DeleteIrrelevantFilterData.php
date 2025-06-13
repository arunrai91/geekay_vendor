<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Helper\FilterSetting as FilterSettingHelper;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Clean up irrelevant data before adding foreign keys
 *
 * Should be executed before Schema, because Schema will add foreign keys.
 * Foreign keys will throw error if data is irrelevant.
 */
class DeleteIrrelevantFilterData implements DataPatchInterface
{
    /**
     * @var FilterSetting
     */
    private $filtersResource;

    public function __construct(
        FilterSetting $filtersResource
    ) {
        $this->filtersResource = $filtersResource;
    }

    /**
     * @return $this
     */
    public function apply()
    {
        if ($this->filtersResource->getConnection()->isTableExists($this->filtersResource->getMainTable())) {
            $this->clearFilters();
        }

        return $this;
    }

    private function clearFilters(): void
    {
        $connection = $this->filtersResource->getConnection();

        if ($connection->tableColumnExists($this->filtersResource->getMainTable(), 'attribute_code')) {
            $this->filterByAttributeCode();
        } else {
            $this->filterByFilterCode();
        }
    }

    private function filterByAttributeCode(): void
    {
        $connection = $this->filtersResource->getConnection();
        $select = $connection->select()->from(
            $this->filtersResource->getTable('eav_attribute'),
            ['attribute_code']
        );
        $connection->delete(
            $this->filtersResource->getMainTable(),
            $connection->quoteInto(FilterSettingInterface::ATTRIBUTE_CODE . ' NOT IN(?)', $select)
        );
    }

    /**
     * Clear filter data by deprecated field filter_code
     *
     * Column attrbiute_code added in version 2.15.1
     */
    private function filterByFilterCode(): void
    {
        $connection = $this->filtersResource->getConnection();
        $select = $connection->select()->from(
            $this->filtersResource->getTable('eav_attribute'),
            [
                'attribute_code' => $connection->getConcatSql(
                    [$connection->quote(FilterSettingHelper::ATTR_PREFIX), 'attribute_code']
                )
            ]
        );
        $connection->delete(
            $this->filtersResource->getMainTable(),
            $connection->quoteInto(FilterSettingInterface::FILTER_CODE . ' NOT IN(?)', $select)
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
