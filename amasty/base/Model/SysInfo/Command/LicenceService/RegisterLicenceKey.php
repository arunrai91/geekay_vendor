<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\SysInfo\Command\LicenceService;

use Amasty\Base\Model\LicenceService\Api\RequestManager;
use Amasty\Base\Model\SysInfo\Command\LicenceService\RegisterLicenceKey\Converter;
use Amasty\Base\Model\SysInfo\Command\LicenceService\RegisterLicenceKey\Domain\Provider;
use Amasty\Base\Model\SysInfo\Command\LicenceService\RegisterLicenceKey\ProcessReRegistration;
use Amasty\Base\Model\SysInfo\Data\RegisteredInstance\Instance;
use Amasty\Base\Model\SysInfo\RegisteredInstanceRepository;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

class RegisterLicenceKey
{
    /**
     * @var RegisteredInstanceRepository
     */
    private $registeredInstanceRepository;

    /**
     * @var RequestManager
     */
    private $requestManager;

    /**
     * @var Provider
     */
    private $domainProvider;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var ProcessReRegistration|null
     */
    private $processReRegistration;

    public function __construct(
        RegisteredInstanceRepository $registeredInstanceRepository,
        RequestManager $requestManager,
        Provider $domainProvider,
        Converter $converter,
        ProcessReRegistration $processReRegistration = null
    ) {
        $this->registeredInstanceRepository = $registeredInstanceRepository;
        $this->requestManager = $requestManager;
        $this->domainProvider = $domainProvider;
        $this->converter = $converter;
        $this->processReRegistration = $processReRegistration
            ?? ObjectManager::getInstance()->get(ProcessReRegistration::class);
    }

    /**
     * @param bool $force
     * @return void
     * @throws LocalizedException
     */
    public function execute(bool $force = false): void
    {
        $currentDomains = $this->domainProvider->getCurrentDomains();
        if (empty($currentDomains)) {
            return;
        }

        if (!$force) {
            $storedDomains = $this->domainProvider->getStoredDomains();
            $domains = array_diff($currentDomains, $storedDomains);
            if (!$domains) {
                return;
            }
        } else {
            $domains = $currentDomains;
        }

        $instance = null;
        $instances = [];
        $registrationCompleted = true;
        $currentInstance = $this->registeredInstanceRepository->get(false)->getCurrentInstance();
        $oldKey = $currentInstance !== null ? $currentInstance->getSystemInstanceKey() : null;
        try {
            foreach ($domains as $domain) {
                $registeredInstanceResponse = $this->requestManager->registerInstance($domain, $oldKey);
                $instanceArray = [
                    Instance::DOMAIN => $domain,
                    Instance::SYSTEM_INSTANCE_KEY => $registeredInstanceResponse->getSystemInstanceKey()
                ];
                $instance = $this->converter->convertArrayToInstance($instanceArray);
                $instances[] = $instance;
            }
        } catch (LocalizedException $exception) {
            $registrationCompleted = false;
        }

        $registeredInstance = $this->registeredInstanceRepository->get();
        $registeredInstance
            ->setCurrentInstance($instance ?? $registeredInstance->getCurrentInstance())
            ->setInstances($this->getUniqueInstances($registeredInstance->getInstances(), $instances));
        $this->registeredInstanceRepository->save($registeredInstance);

        if (!$registrationCompleted) {
            throw new LocalizedException(__('Registration failed, please try again later.'));
        }
        if ($force || $oldKey) {
            $this->processReRegistration->execute($registeredInstance->getCurrentInstance());
        }
    }

    /**
     * @param Instance[] $existInstances
     * @param Instance[] $newInstances
     * @return Instance[]
     */
    private function getUniqueInstances(array $existInstances, array $newInstances): array
    {
        $result = [];
        foreach (array_merge($newInstances, $existInstances) as $instance) {
            if (!isset($result[$instance->getDomain()])) {
                $result[$instance->getDomain()] = $instance;
            }
        }

        return array_values($result);
    }
}
