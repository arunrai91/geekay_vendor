<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Api\IpLog;

use Amasty\Geoip\Api\Data\IpLogInterface;
use Magento\Framework\Exception\CouldNotSaveException;

interface SaveInterface
{
    /**
     * @param IpLogInterface $ipLog
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(IpLogInterface $ipLog): void;

    /**
     * @param IpLogInterface[] $ipLogs
     * @return void
     * @throws CouldNotSaveException
     */
    public function executeMultiple(array $ipLogs): void;
}
