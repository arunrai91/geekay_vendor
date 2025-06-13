<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ScheduleRepositoryInterface
{
    /**
     * @param string $code
     * @return \Amasty\Base\Api\Data\ScheduleInterface
     * @throws NoSuchEntityException
     */
    public function get(string $code): Data\ScheduleInterface;

    /**
     * @param \Amasty\Base\Api\Data\ScheduleInterface $schedule
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(Data\ScheduleInterface $schedule): void;

    /**
     * @param string $code
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(string $code): void;
}
