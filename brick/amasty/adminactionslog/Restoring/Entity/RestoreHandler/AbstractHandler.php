<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring\Entity\RestoreHandler;

use Amasty\AdminActionsLog\Api\Data\LogDetailInterface;
use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Api\Restoring\EntityRestoreHandlerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractHandler implements EntityRestoreHandlerInterface
{
    public const STORAGE_CODE_PREFIX = 'action_type';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ObjectDataStorageInterface
     */
    protected $dataStorage;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectDataStorageInterface $dataStorage,
        StoreManagerInterface $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->dataStorage = $dataStorage;
        $this->storeManager = $storeManager;
    }

    protected function getModelObject(LogEntryInterface $logEntry, LogDetailInterface $logDetail)
    {
        $elementId = $logEntry->getElementId();
        $modelName = $logDetail->getModel();

        return $this->objectManager->create($modelName)->load($elementId);
    }

    protected function setRestoreActionFlag($object): void
    {
        $storageKey = spl_object_id($object) . '.' . self::STORAGE_CODE_PREFIX;
        $this->dataStorage->set($storageKey, []);
    }

    /**
     * Set current default store
     *
     * @param string|int|\Magento\Store\Api\Data\StoreInterface $store
     * @return void
     */
    protected function setCurrentStore($store = Store::DEFAULT_STORE_ID): void
    {
        $this->storeManager->setCurrentStore($store);
    }
}
