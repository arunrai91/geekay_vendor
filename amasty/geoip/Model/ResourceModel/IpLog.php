<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\ResourceModel;

use Amasty\Geoip\Api\Data\IpLogInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class IpLog extends AbstractDb
{
    public const TABLE_NAME = 'amasty_geoip_ip_log';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, IpLogInterface::LOG_ID);
    }

    public function insertOnDuplicate(IpLogInterface $ipLog): int
    {
        return $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            $ipLog->toArray()
        );
    }

    /**
     * @param IpLogInterface[] $ipLogs
     * @return int
     */
    public function insertMultiple(array $ipLogs): int
    {
        return $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            array_map(static function ($ipLog) {
                return $ipLog->toArray();
            }, $ipLogs)
        );
    }

    public function deleteByLastVisitOlderThan(string $date): int
    {
        return $this->getConnection()->delete(
            $this->getMainTable(),
            [IpLogInterface::LAST_VISIT . ' < ?' => $date]
        );
    }
}
