<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\ResourceModel;

use Amasty\Geoip\Api\Data\LocationInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Location extends AbstractDb
{
    public const TABLE_NAME = 'amasty_geoip_location';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, LocationInterface::LOCATION_ID);
    }
}
