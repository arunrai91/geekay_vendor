<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\SysInfo\Command\LicenceService\RegisterLicenceKey;

use Amasty\Base\Model\InstanceData\Repository as InstanceDataRepository;
use Amasty\Base\Model\LicenceService\Api\RequestManager;
use Amasty\Base\Model\Schedule\Repository as ScheduleRepository;
use Amasty\Base\Model\SysInfo\Command\LicenceService\ProcessLicenseValidationResponse;
use Amasty\Base\Model\SysInfo\Command\LicenceService\SendSysInfo\ChangedData\Persistor as ChangedDataPersistor;
use Amasty\Base\Model\SysInfo\Command\LicenceService\SendSysInfo\Converter;
use Amasty\Base\Model\SysInfo\Data\LicenseValidationFactory;
use Amasty\Base\Model\SysInfo\Data\RegisteredInstance\Instance;
use Amasty\Base\Model\SysInfo\LicenseValidationRepository;
use Amasty\Base\Model\SysInfo\Provider\Collector;
use Amasty\Base\Model\SysInfo\Provider\CollectorPool;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * License checker data processing.
 * Removes saved response & runs collect request
 */
class ProcessReRegistration
{
    /**
     * @var LicenseValidationRepository
     */
    private $licenseValidationRepository;

    /**
     * @var InstanceDataRepository
     */
    private $instanceDataRepository;

    /**
     * @var ScheduleRepository
     */
    private $scheduleRepository;

    /**
     * @var LicenseValidationFactory
     */
    private $licenseValidationFactory;

    /**
     * @var ChangedDataPersistor
     */
    private $changedDataPersistor;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var RequestManager
     */
    private $requestManager;

    /**
     * @var Collector
     */
    private $collector;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LicenseValidationRepository $licenseValidationRepository,
        LicenseValidationFactory $licenseValidationFactory,
        InstanceDataRepository $instanceDataRepository,
        ScheduleRepository $scheduleRepository,
        ChangedDataPersistor $changedDataPersistor,
        Converter $converter,
        RequestManager $requestManager,
        Collector $collector,
        LoggerInterface $logger
    ) {
        $this->licenseValidationRepository = $licenseValidationRepository;
        $this->instanceDataRepository = $instanceDataRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->licenseValidationFactory = $licenseValidationFactory;
        $this->changedDataPersistor = $changedDataPersistor;
        $this->converter = $converter;
        $this->requestManager = $requestManager;
        $this->collector = $collector;
        $this->logger = $logger;
    }

    public function execute(Instance $registeredInstance): void
    {
        $this->invalidateLicenses();
        $this->sendCollectRequest($registeredInstance);
    }

    private function invalidateLicenses(): void
    {
        $currentLicense = $this->licenseValidationRepository->get();
        if ($currentLicense->isNeedCheckLicense() === true) {
            $newLicense = $this->licenseValidationFactory->create();
            $newLicense->setIsNeedCheckLicense(true);
            //remove all fields except 'need to check'
            $this->licenseValidationRepository->save($newLicense);
        } else {
            try {
                $this->instanceDataRepository->delete(LicenseValidationRepository::FLAG_KEY);
                $this->scheduleRepository->delete(ProcessLicenseValidationResponse::FLAG_NAME);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    private function sendCollectRequest(Instance $registeredInstance): void
    {
        $instanceData = $this->collector->collect(CollectorPool::LICENCE_SERVICE_GROUP);
        $instanceInfo = $this->converter->convertToObject(
            $instanceData
        );
        $instanceInfo->setSystemInstanceKey($registeredInstance->getSystemInstanceKey());
        $instanceInfo->setDomain($registeredInstance->getDomain());
        try {
            $this->requestManager->updateInstanceInfo($instanceInfo);
            $this->changedDataPersistor->save($instanceData);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
