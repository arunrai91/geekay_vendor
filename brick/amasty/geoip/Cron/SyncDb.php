<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Cron;

use Amasty\Geoip\Api\Data\IpLogInterface;
use Amasty\Geoip\Api\IpLog\SaveInterface as IpLogSaver;
use Amasty\Geoip\Api\TablePatchApplierInterface;
use Amasty\Geoip\Exceptions\LicenseInvalidException;
use Amasty\Geoip\Model\ConfigProvider;
use Amasty\Geoip\Model\IpLog\UpdateBatcher;
use Amasty\Geoip\Model\Source\RefreshIpBehaviour;
use Amasty\Geoip\Model\SyncService\Patch\Collector;
use Amasty\Geoip\Model\System\Message\LicenseInvalid as LicenseInvalidMessage;
use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class SyncDb
{
    /**
     * @var UpdateBatcher
     */
    private $updateBatcher;

    /**
     * @var Collector
     */
    private $patchTablesDataCollector;

    /**
     * @var TablePatchApplierInterface
     */
    private $patchApplier;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IpLogSaver
     */
    private $ipLogSaver;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LicenseInvalidMessage
     */
    private $licenseInvalidMessage;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        UpdateBatcher $updateBatcher,
        Collector $patchTablesDataCollector,
        TablePatchApplierInterface $patchApplier,
        ResourceConnection $resource,
        IpLogSaver $ipLogSaver,
        DateTime $dateTime,
        LoggerInterface $logger,
        ?LicenseInvalidMessage $licenseInvalidMessage = null, // TODO move to not optional
        ?ConfigProvider $configProvider = null // TODO move to not optional
    ) {
        $this->updateBatcher = $updateBatcher;
        $this->patchTablesDataCollector = $patchTablesDataCollector;
        $this->patchApplier = $patchApplier;
        $this->resource = $resource;
        $this->ipLogSaver = $ipLogSaver;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->licenseInvalidMessage = $licenseInvalidMessage
            ?? ObjectManager::getInstance()->get(LicenseInvalidMessage::class);
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
    }

    public function execute(): void
    {
        if ($this->configProvider->getRefreshIpBehaviour() === RefreshIpBehaviour::MANUALLY) {
            return;
        }

        $connection = $this->resource->getConnection();

        while ($ipLogBatch = $this->updateBatcher->getBatch()) {
            try {
                $connection->beginTransaction();

                $this->executePatching($ipLogBatch);
                $this->updateLastSync($ipLogBatch);

                $connection->commit();
            } catch (LicenseInvalidException $exception) {
                $connection->rollBack();
                $this->licenseInvalidMessage->setIsDisplayed(true);
            } catch (Exception $exception) {
                $connection->rollBack();
                $this->logger->error($exception);
            }
        }
    }

    /**
     * @param IpLogInterface[] $ipLogBatch
     * @return void
     */
    private function executePatching(array $ipLogBatch): void
    {
        $patchData = $this->patchTablesDataCollector->collect($ipLogBatch);
        $this->patchApplier->apply($patchData);
    }

    /**
     * @param IpLogInterface[] $ipLogBatch
     * @return void
     */
    private function updateLastSync(array $ipLogBatch): void
    {
        foreach ($ipLogBatch as $ipLog) {
            $ipLog->setLastSync($this->dateTime->gmtDate('Y-m-d'));
        }

        $this->ipLogSaver->executeMultiple($ipLogBatch);
    }
}
