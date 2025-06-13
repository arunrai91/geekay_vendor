<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Schedule;

use Amasty\Base\Api\Data\ScheduleInterface;
use Amasty\Base\Api\Data\ScheduleInterfaceFactory;
use Amasty\Base\Api\ScheduleRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements ScheduleRepositoryInterface
{
    /**
     * @var ResourceModel\Schedule
     */
    private $scheduleResource;

    /**
     * @var ScheduleInterfaceFactory
     */
    private $scheduleFactory;

    public function __construct(
        ResourceModel\Schedule $instanceDataResource,
        ScheduleInterfaceFactory $scheduleFactory
    ) {
        $this->scheduleResource = $instanceDataResource;
        $this->scheduleFactory = $scheduleFactory;
    }

    public function get(string $code): ScheduleInterface
    {
        $schedule = $this->scheduleFactory->create();
        $this->scheduleResource->load($schedule, $code, ScheduleInterface::CODE);
        if (!$schedule->getId()) {
            throw new NoSuchEntityException(__('Instance Data with code "%1" not found.', $code));
        }

        return $schedule;
    }

    public function save(ScheduleInterface $schedule): void
    {
        try {
            try {
                /** @var Schedule $scheduleSaved */
                $scheduleSaved = $this->get($schedule->getCode());
                $scheduleSaved->addData($schedule->getData());
                $schedule = $scheduleSaved;
                //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (NoSuchEntityException $e) {
            }
            $this->scheduleResource->save($schedule);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save the Schedule. Error: %1', $e->getMessage()));
        }
    }

    public function delete(string $code): void
    {
        try {
            $schedule = $this->get($code);
            $this->scheduleResource->delete($schedule);
            //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete Schedule with code %1. Error: "%2"', $code, $e->getMessage())
            );
        }
    }
}
