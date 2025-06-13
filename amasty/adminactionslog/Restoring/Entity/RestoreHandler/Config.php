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
use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Logging\Util\DetailsBuilder;
use Amasty\AdminActionsLog\Model\LogEntry\AdminLogEntryFactory;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config extends AbstractHandler
{
    /**
     * @var ResourceConfig
     */
    private $scopeConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $resourceConfig;

    /**
     * @var AdminLogEntryFactory
     */
    private $logEntryFactory;

    /**
     * @var DetailsBuilder
     */
    private $detailsBuilder;

    /**
     * @var LogEntryRepositoryInterface
     */
    private $logEntryRepository;

    public function __construct(
        ResourceConfig $resourceConfig,
        ScopeConfigInterface $scopeConfig,
        AdminLogEntryFactory $logEntryFactory,
        DetailsBuilder $detailsBuilder,
        LogEntryRepositoryInterface $logEntryRepository,
        ObjectManagerInterface $objectManager,
        ObjectDataStorageInterface $dataStorage,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $objectManager,
            $dataStorage,
            $storeManager
        );

        $this->resourceConfig = $resourceConfig;
        $this->scopeConfig = $scopeConfig;
        $this->logEntryFactory = $logEntryFactory;
        $this->detailsBuilder = $detailsBuilder;
        $this->logEntryRepository = $logEntryRepository;
    }

    public function restore(LogEntryInterface $logEntry, array $logDetails): void
    {
        $beforeData = $afterData = [];

        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $additionalData = $logEntry->getAdditionalData();
        // $logEntry->getStoreId() can return website id or store view id.
        // So we need to know scope to load correct beforeData
        if ($additionalData && isset($additionalData['scope'])) {
            $scope = $additionalData['scope'];
        }

        /** @var LogDetailInterface $logDetail */
        foreach ($logDetails as $logDetail) {
            $oldValue = $logDetail->getOldValue();
            $elementKey = $logDetail->getName();
            $beforeData[$elementKey] = $this->scopeConfig->getValue(
                $elementKey,
                $scope,
                (int)$logEntry->getStoreId()
            );
            $afterData[$elementKey] = $oldValue;

            $this->resourceConfig->saveConfig(
                $elementKey,
                $oldValue,
                $scope,
                (int)$logEntry->getStoreId()
            );
        }

        if ($detailsList = $this->detailsBuilder->build(Value::class, $beforeData, $afterData)) {
            $newLogEntry = $this->logEntryFactory->create(
                [
                    LogEntry::TYPE => LogEntryTypes::TYPE_RESTORE
                ]
            );
            $newLogEntry->setCategory($logEntry->getCategory())
                ->setCategoryName($logEntry->getCategoryName())
                ->setParameterName($logEntry->getParameterName())
                ->setElementId($logEntry->getElementId())
                ->setLogDetails($detailsList)
                ->setStoreId((int)$logEntry->getStoreId());
            $this->logEntryRepository->save($newLogEntry);
        }
    }
}
