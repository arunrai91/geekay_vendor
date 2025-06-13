<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\SysInfo;

use Amasty\Base\Model\SysInfo\RegisteredInstanceRepository;

class InstanceIdProvider
{
    /**
     * @var RegisteredInstanceRepository
     */
    private $registeredInstanceRepository;

    /**
     * @var string|null
     */
    private $instanceId;

    public function __construct(
        RegisteredInstanceRepository $registeredInstanceRepository
    ) {
        $this->registeredInstanceRepository = $registeredInstanceRepository;
    }

    public function getInstanceId(): ?string
    {
        if ($this->instanceId === null) {
            $registeredInstance = $this->registeredInstanceRepository->get()->getCurrentInstance();
            $this->instanceId = $registeredInstance
                ? $registeredInstance->getSystemInstanceKey()
                : null;
        }

        return $this->instanceId;
    }

    public function _resetState(): void
    {
        $this->instanceId = null;
    }
}
