<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Cron;

use Amasty\Geoip\Api\IpLog\DeleteInterface;
use Exception;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class ClearIpLogs
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var DeleteInterface
     */
    private $deleter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DateTime $dateTime,
        DeleteInterface $deleter,
        LoggerInterface $logger
    ) {
        $this->dateTime = $dateTime;
        $this->deleter = $deleter;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $this->deleter->deleteByLastVisitOlderThan($this->dateTime->gmtDate('Y-m-d', '-1 month'));
        } catch (Exception $exception) {
            $this->logger->error($exception);
        }
    }
}
