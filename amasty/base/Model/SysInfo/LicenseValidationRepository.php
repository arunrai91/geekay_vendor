<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\SysInfo;

use Amasty\Base\Model\FlagRepository;
use Amasty\Base\Model\InstanceData\InstanceDataFactory;
use Amasty\Base\Model\InstanceData\Repository;
use Amasty\Base\Model\Serializer;
use Amasty\Base\Model\SysInfo\Command\LicenceService\ProcessLicenseRegistrationResponse\Converter;
use Amasty\Base\Model\SysInfo\Data\LicenseValidation;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

class LicenseValidationRepository
{
    public const FLAG_KEY = 'amasty_base_license_validation_response';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var LicenseValidation|null
     */
    private $loadedEntity;

    /**
     * @var Repository
     */
    private $instanceDataRepository;

    /**
     * @var InstanceDataFactory
     */
    private $instanceDataFactory;

    public function __construct(
        FlagRepository $flagRepository = null, //@deprecated
        Serializer $serializer,
        Converter $converter,
        Repository $instanceDataRepository = null,
        InstanceDataFactory $instanceDataFactory = null
    ) {
        $this->serializer = $serializer;
        $this->converter = $converter;
        $this->instanceDataRepository = $instanceDataRepository
            ?? ObjectManager::getInstance()->get(Repository::class);
        $this->instanceDataFactory = $instanceDataFactory
            ?? ObjectManager::getInstance()->get(InstanceDataFactory::class);
    }

    public function save(LicenseValidation $licenseValidation): void
    {
        $instanceData = $this->instanceDataFactory->create();
        $licenseResponseSerialized = $this->serializer->serialize($licenseValidation->toArray());

        $instanceData->setCode(self::FLAG_KEY);
        $instanceData->setValue($licenseResponseSerialized);
        $this->instanceDataRepository->save($instanceData);

        $this->loadedEntity = $licenseValidation;
    }

    public function get(bool $reload = false): LicenseValidation
    {
        if ($reload) {
            $this->loadedEntity = null;
        }

        if (!$this->loadedEntity) {
            try {
                $instanceData = $this->instanceDataRepository->get(self::FLAG_KEY);
                $licenseResponseSerialized = $instanceData->getValue();
            } catch (NoSuchEntityException $e) {
                $licenseResponseSerialized = null;
            }
            $licenseResponseArray = $licenseResponseSerialized
                ? $this->serializer->unserialize($licenseResponseSerialized)
                : [];
            $this->loadedEntity = $this->converter->convertArrayToEntity($licenseResponseArray);
        }

        return $this->loadedEntity;
    }
}
