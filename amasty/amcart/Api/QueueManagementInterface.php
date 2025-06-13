<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Api;

use Amasty\Acart\Api\Data\HistoryInterface;

interface QueueManagementInterface
{
    /**
     * @param HistoryInterface $history
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function markAsDeleted(HistoryInterface $history): HistoryInterface;

    /**
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function markAsDeletedById(int $id): HistoryInterface;
}
