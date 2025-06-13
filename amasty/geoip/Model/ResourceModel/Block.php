<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\ResourceModel;

use Amasty\Geoip\Api\Data\BlockInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Block extends AbstractDb
{
    public const TABLE_NAME = 'amasty_geoip_block';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, BlockInterface::BLOCK_ID);
    }
}
