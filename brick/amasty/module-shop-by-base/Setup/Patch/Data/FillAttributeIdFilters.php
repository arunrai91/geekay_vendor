<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Setup\Patch\Data;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update Filter attribute ID values by related attribute.
 */
class FillAttributeIdFilters implements DataPatchInterface
{
    /**
     * @var FilterSetting
     */
    private $filterResource;

    public function __construct(
        FilterSetting $filterResource
    ) {
        $this->filterResource = $filterResource;
    }

    /**
     * @return $this
     */
    public function apply()
    {
        $connection = $this->filterResource->getConnection();

        $select = $connection->select()->join(
            ['eav' => $this->filterResource->getTable('eav_attribute')],
            'eav.attribute_code = filter.attribute_code',
            [
                FilterSettingInterface::ATTRIBUTE_ID => 'eav.attribute_id',
            ]
        );
        $query = $connection->updateFromSelect($select, ['filter' => $this->filterResource->getMainTable()]);
        $connection->query($query);

        return $this;
    }

    public static function getDependencies(): array
    {
        return [\Amasty\ShopbyBase\Setup\Patch\Data\FillAttributeCodeColumn::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
