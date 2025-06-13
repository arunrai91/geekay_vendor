<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\History;

use Amasty\Acart\Api\Data\HistoryInterface;
use Amasty\Acart\Api\Data\HistoryInterfaceFactory;
use Amasty\Acart\Api\HistoryRepositoryInterface;
use Amasty\Acart\Model\AbstractCachedRepository;
use Amasty\Acart\Model\History as HistoryModel;
use Amasty\Acart\Model\ResourceModel\History as HistoryResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NotFoundException;

class Repository extends AbstractCachedRepository implements HistoryRepositoryInterface
{
    /**
     * @var HistoryInterfaceFactory
     */
    private $historyFactory;

    /**
     * @var HistoryResource
     */
    private $historyResource;

    public function __construct(
        HistoryInterfaceFactory $historyFactory,
        HistoryResource $historyResource
    ) {
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
    }

    private function getBy($value, $field = HistoryModel::HISTORY_ID): HistoryInterface
    {
        if (($result = $this->getFromCache($field, $value)) !== null) {
            return $result;
        }

        /** @var HistoryInterface $history */
        $history = $this->historyFactory->create();
        $this->historyResource->load($history, $value, $field);
        if (!$history->getHistoryId()) {
            throw new NotFoundException(
                __('History with specified %1 "%2"  not found.', $field, $value)
            );
        }

        return $this->addToCache($field, $value, $history);
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): HistoryInterface
    {
        return $this->getBy($id, HistoryModel::HISTORY_ID);
    }

    /**
     * @inheritdoc
     */
    public function getByCouponCode(string $couponCode): HistoryInterface
    {
        return $this->getBy($couponCode, HistoryModel::SALES_RULE_COUPON);
    }

    /**
     * @inheritdoc
     */
    public function save(HistoryInterface $history): HistoryInterface
    {
        try {
            $this->historyResource->save($history);
            $this->invalidateCache($history);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Unable to save the history. Error: %1', $e->getMessage())
            );
        }

        return $history;
    }

    /**
     * @inheritdoc
     */
    public function delete(HistoryInterface $history): bool
    {
        try {
            $this->historyResource->delete($history);
            $this->invalidateCache($history);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Unable to delete the history. Error: %1', $e->getMessage())
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $id): bool
    {
        $this->delete($this->getById($id));

        return true;
    }
}
