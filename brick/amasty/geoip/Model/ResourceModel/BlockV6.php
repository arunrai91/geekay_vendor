<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\ResourceModel;

use Amasty\Geoip\Api\Data\BlockV6Interface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BlockV6 extends AbstractDb
{
    public const TABLE_NAME = 'amasty_geoip_block_v6';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, BlockV6Interface::BLOCK_ID);
    }
}
