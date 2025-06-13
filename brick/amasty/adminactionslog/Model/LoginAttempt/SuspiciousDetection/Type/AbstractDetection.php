<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\SuspiciousDetection\Type;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Model\LoginAttempt\LoginAttempt;
use Amasty\AdminActionsLog\Model\LoginAttempt\ResourceModel\CollectionFactory;
use Amasty\AdminActionsLog\Model\OptionSource\LoginAttemptStatus;

abstract class AbstractDetection implements DetectorInterface
{
    /**
     * @var CollectionFactory
     */
    private $loginAttemptCollectionFactory;

    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    public function __construct(
        CollectionFactory $loginAttemptCollectionFactory,
        ObjectDataStorageInterface $dataStorage
    ) {
        $this->loginAttemptCollectionFactory = $loginAttemptCollectionFactory;
        $this->dataStorage = $dataStorage;
    }

    abstract public function isSuspiciousAttempt(LoginAttemptInterface $loginAttempt): bool;

    public function getLastSucceedAttempt(LoginAttemptInterface $loginAttempt): LoginAttemptInterface
    {
        $storageKey = spl_object_id($loginAttempt) . '.none';
        if ($this->dataStorage->isExists($storageKey)) {
            $lastLoginAttempt = $this->dataStorage->get($storageKey);

            return array_shift($lastLoginAttempt);
        }

        $loginAttemptCollection = $this->loginAttemptCollectionFactory->create();

        $lastLoginAttempt = $loginAttemptCollection->addFieldToFilter(
            LoginAttempt::USERNAME,
            $loginAttempt->getUsername()
        )->addFieldToFilter(LoginAttempt::STATUS, LoginAttemptStatus::SUCCESS)
            ->addFieldToFilter($loginAttemptCollection->getIdFieldName(), ['neq' => $loginAttempt->getId()])
            ->setOrder($loginAttemptCollection->getIdFieldName(), $loginAttemptCollection::SORT_ORDER_DESC)
            ->setPageSize(1)
            ->getFirstItem();
        $this->dataStorage->set($storageKey, [$lastLoginAttempt]);

        return $lastLoginAttempt;
    }
}
