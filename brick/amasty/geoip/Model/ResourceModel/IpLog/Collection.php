<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\ResourceModel\IpLog;

use Amasty\Geoip\Model\IpLog;
use Amasty\Geoip\Model\ResourceModel\IpLog as IpLogResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(IpLog::class, IpLogResource::class);
    }
}
