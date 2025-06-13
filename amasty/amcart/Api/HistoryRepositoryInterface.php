<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Api;

use Amasty\Acart\Api\Data\HistoryInterface;

interface HistoryRepositoryInterface
{
    /**
     * Get history by ID.
     *
     * @param int $id
     *
     * @return \Amasty\Acart\Api\Data\HistoryInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getById(int $id): HistoryInterface;

    /**
     * Get history by Coupon Code.
     *
     * @param string $couponCode
     *
     * @return \Amasty\Acart\Api\Data\HistoryInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getByCouponCode(string $couponCode): HistoryInterface;

    /**
     * Save history.
     *
     * @param \Amasty\Acart\Api\Data\HistoryInterface $history
     *
     * @return \Amasty\Acart\Api\Data\HistoryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(HistoryInterface $history): HistoryInterface;

    /**
     * Delete history.
     *
     * @param \Amasty\Acart\Api\Data\HistoryInterface $history
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(HistoryInterface $history): bool;

    /**
     * Delete history by ID.
     *
     * @param int $id
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $id): bool;
}
