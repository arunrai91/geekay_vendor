<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\IpLog\Repository;

use Amasty\Geoip\Api\Data\IpLogInterface;
use Amasty\Geoip\Api\IpLog\SaveInterface;
use Amasty\Geoip\Model\ResourceModel\IpLog as IpLogResource;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;

class Save implements SaveInterface
{
    /**
     * @var IpLogResource
     */
    private $ipLogResource;

    public function __construct(IpLogResource $ipLogResource)
    {
        $this->ipLogResource = $ipLogResource;
    }

    public function execute(IpLogInterface $ipLog): void
    {
        try {
            $this->ipLogResource->insertOnDuplicate($ipLog);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save Ip Log Entity %1', $exception->getMessage())
            );
        }
    }

    public function executeMultiple(array $ipLogs): void
    {
        try {
            $this->ipLogResource->insertMultiple($ipLogs);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save Ip Log Entities %1', $exception->getMessage())
            );
        }
    }
}
