<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Schedule;

use Amasty\Base\Api\Data\ScheduleInterface;
use Magento\Framework\Model\AbstractModel;

class Schedule extends AbstractModel implements ScheduleInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Schedule::class);
        $this->setIdFieldName(ScheduleInterface::ID);
    }

    public function getId(): ?int
    {
        return $this->hasData(ScheduleInterface::ID)
            ? (int)$this->_getData(ScheduleInterface::ID)
            : null;
    }

    public function setCode(string $code): void
    {
        $this->setData(ScheduleInterface::CODE, $code);
    }

    public function getCode(): string
    {
        return $this->_getData(ScheduleInterface::CODE);
    }

    public function setLastSendDate(int $timestamp): void
    {
        $this->setData(ScheduleInterface::LAST_SEND_DATE, $timestamp);
    }

    public function getLastSendDate(): int
    {
        return (int)$this->_getData(ScheduleInterface::LAST_SEND_DATE);
    }

    public function setTimeIntervals(?string $intervals): void
    {
        $this->setData(ScheduleInterface::TIME_INTERVALS, $intervals);
    }

    public function getTimeIntervals(): ?string
    {
        return $this->hasData(ScheduleInterface::TIME_INTERVALS)
            ? (string)$this->_getData(ScheduleInterface::TIME_INTERVALS)
            : null;
    }
}
