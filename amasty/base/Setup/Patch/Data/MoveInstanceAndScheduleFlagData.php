<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Setup\Patch\Data;

use Amasty\Base\Api\Data\InstanceDataInterfaceFactory;
use Amasty\Base\Api\Data\ScheduleInterfaceFactory;
use Amasty\Base\Api\InstanceDataRepositoryInterface;
use Amasty\Base\Api\ScheduleRepositoryInterface;
use Amasty\Base\Cron\DailySendSystemInfo;
use Amasty\Base\Cron\InstanceRegistration;
use Amasty\Base\Model\FlagRepository;
use Amasty\Base\Model\LicenceService\Schedule\Data\ScheduleConfig;
use Amasty\Base\Model\Serializer;
use Amasty\Base\Model\SysInfo\Command\LicenceService\ProcessLicenseValidationResponse;
use Amasty\Base\Model\SysInfo\LicenseValidationRepository;
use Amasty\Base\Model\SysInfo\RegisteredInstanceRepository;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class MoveInstanceAndScheduleFlagData implements DataPatchInterface
{
    private const INSTANCE_FLAG_CODES = [
        RegisteredInstanceRepository::REGISTERED_INSTANCE,
        LicenseValidationRepository::FLAG_KEY
    ];

    private const SCHEDULE_FLAG_CODES = [
        InstanceRegistration::FLAG_KEY,
        DailySendSystemInfo::FLAG_KEY,
        ProcessLicenseValidationResponse::FLAG_NAME
    ];

    /**
     * @var FlagResource
     */
    private $flagResource;

    /**
     * @var FlagRepository
     */
    private $flagRepository;

    /**
     * @var InstanceDataRepositoryInterface
     */
    private $instanceDataRepository;

    /**
     * @var InstanceDataInterfaceFactory
     */
    private $instanceDataFactory;

    /**
     * @var ScheduleRepositoryInterface
     */
    private $scheduleRepository;

    /**
     * @var ScheduleInterfaceFactory
     */
    private $scheduleFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FlagResource $flagResource,
        FlagRepository $flagRepository,
        InstanceDataRepositoryInterface $instanceDataRepository,
        InstanceDataInterfaceFactory $instanceDataFactory,
        ScheduleRepositoryInterface $scheduleRepository,
        ScheduleInterfaceFactory $scheduleFactory,
        Serializer $serializer,
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger
    ) {
        $this->flagResource = $flagResource;
        $this->flagRepository = $flagRepository;
        $this->instanceDataRepository = $instanceDataRepository;
        $this->instanceDataFactory = $instanceDataFactory;
        $this->scheduleRepository = $scheduleRepository;
        $this->scheduleFactory = $scheduleFactory;
        $this->serializer = $serializer;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->beginTransaction();
        try {
            $this->moveFlagData(self::INSTANCE_FLAG_CODES, [$this, 'moveInstanceFlagData']);
            $this->moveFlagData(self::SCHEDULE_FLAG_CODES, [$this, 'moveScheduleFlagData']);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    private function moveFlagData(array $flagCodes, callable $callback): void
    {
        foreach ($flagCodes as $code) {
            $flagData = $this->flagRepository->get($code);
            if ($flagData) {
                $callback($code, $flagData);
            }
        }
    }

    private function moveInstanceFlagData(string $code, string $value): void
    {
        $instanceData = $this->instanceDataFactory->create();
        $instanceData->setCode($code);
        $instanceData->setValue($value);
        try {
            $this->instanceDataRepository->save($instanceData);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return;
        }

        $this->flagResource->getConnection()->delete(
            $this->flagResource->getMainTable(),
            ['flag_code = ?' => $code]
        );
    }

    private function moveScheduleFlagData(string $code, string $value): void
    {
        $scheduleData = $this->serializer->unserialize($value);
        if ($scheduleData) {
            /** @var \Amasty\Base\Api\Data\ScheduleInterface $schedule */
            $schedule = $this->scheduleFactory->create();
            $schedule->setCode($code);
            $intervals = implode(',', $scheduleData[ScheduleConfig::TIME_INTERVALS] ?? []);
            $schedule->setTimeIntervals($intervals);
            $schedule->setLastSendDate((int)($scheduleData[ScheduleConfig::LAST_SEND_DATE] ?? 0));
            try {
                $this->scheduleRepository->save($schedule);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                return;
            }
        }

        $this->flagResource->getConnection()->delete(
            $this->flagResource->getMainTable(),
            ['flag_code = ?' => $code]
        );
    }
}
