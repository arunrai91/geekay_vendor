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
use Amasty\Base\Model\SysInfo\Data\RegisteredInstance;
use Amasty\Base\Model\SysInfo\Data\RegisteredInstanceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;

class RegisteredInstanceRepository
{
    public const REGISTERED_INSTANCE = 'amasty_base_registered_instance';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var RegisteredInstanceFactory
     */
    private $registeredInstanceFactory;

    /**
     * @var Repository
     */
    private $instanceDataRepository;

    /**
     * @var InstanceDataFactory
     */
    private $instanceDataFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        FlagRepository $flagRepository = null, //@deprecated
        SerializerInterface $serializer,
        DataObjectHelper $dataObjectHelper,
        RegisteredInstanceFactory $registeredInstanceFactory,
        Repository $instanceDataRepository = null,
        InstanceDataFactory $instanceDataFactory = null,
        UrlInterface $url = null
    ) {
        $this->serializer = $serializer;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->registeredInstanceFactory = $registeredInstanceFactory;
        $this->instanceDataRepository = $instanceDataRepository
            ?? ObjectManager::getInstance()->get(Repository::class);
        $this->instanceDataFactory = $instanceDataFactory
            ?? ObjectManager::getInstance()->get(InstanceDataFactory::class);
        $this->url = $url
            ?? ObjectManager::getInstance()->get(UrlInterface::class);
    }

    public function get(bool $checkDomain = true): RegisteredInstance
    {
        $registeredInstance = $this->registeredInstanceFactory->create();
        try {
            $instanceData = $this->instanceDataRepository->get(self::REGISTERED_INSTANCE);
            $regInstSerialized = $instanceData->getValue();
        } catch (NoSuchEntityException $e) {
            $regInstSerialized = null;
        }

        $regInstArray = $regInstSerialized ? $this->serializer->unserialize($regInstSerialized) : [];
        $this->dataObjectHelper->populateWithArray(
            $registeredInstance,
            $regInstArray,
            RegisteredInstance::class
        );

        return ($checkDomain && $this->isDomainChanged($registeredInstance))
            ? $this->registeredInstanceFactory->create()
            : $registeredInstance;
    }

    public function save(RegisteredInstance $registeredInstance): bool
    {
        $instanceData = $this->instanceDataFactory->create();
        $regInstSerialized = $this->serializer->serialize($registeredInstance->toArray());

        $instanceData->setCode(self::REGISTERED_INSTANCE);
        $instanceData->setValue($regInstSerialized);
        $this->instanceDataRepository->save($instanceData);

        return true;
    }

    private function isDomainChanged(RegisteredInstance $registeredInstance): bool
    {
        $currentInstance = $registeredInstance->getCurrentInstance();
        $registeredDomain = $currentInstance ? $currentInstance->getDomain() : '';
        // phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
        $currentDomain = parse_url(
            $this->url->getBaseUrl(['_scope' => Store::DEFAULT_STORE_ID]),
            PHP_URL_HOST
        );

        return $registeredDomain !== $currentDomain || empty($currentDomain);
    }
}
