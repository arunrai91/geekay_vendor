<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\SyncService\Headers;

use Amasty\Base\Model\SysInfo\Command\LicenceService\GetCurrentLicenseValidation;
use Amasty\Geoip\Model\SysInfo\InstanceIdProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;

class AddInstanceIdHeader
{
    public const HEADER_KEY = 'X-INSTANCE-ID';

    /**
     * @var InstanceIdProvider
     */
    private $instanceIdProvider;

    /**
     * @var GetCurrentLicenseValidation
     */
    private $currentLicenseValidation;

    public function __construct(
        InstanceIdProvider $instanceIdProvider,
        GetCurrentLicenseValidation $currentLicenseValidation
    ) {
        $this->instanceIdProvider = $instanceIdProvider;
        $this->currentLicenseValidation = $currentLicenseValidation;
    }

    /**
     * @throws LocalizedException
     */
    public function add(Curl $curl): void
    {
        $instanceKey = $this->instanceIdProvider->getInstanceId();
        if (empty($instanceKey)) {
            throw new LocalizedException(
                __(
                    'This instance hasn\'t been registered yet. Please register your instance by running'
                    . ' "php bin/magento amasty-base:licence:register-key" and '
                    . ' "php bin/magento amasty-base:licence:send-sys-info" commands. '
                    . 'Alternatively, the instance will be registered by cron job in up to 24 hours.'
                )
            );
        }
        if ($this->currentLicenseValidation->get()->isNeedCheckLicense() !== true) {
            throw new LocalizedException(
                __(
                    'License Registration functionality is not enabled for this instance yet.'
                    . ' Please %1 our Support Team for further assistance.',
                    '<a href="https://amasty.com/contacts/" target="_blank">contact</a>'
                )
            );
        }
        $curl->addHeader(self::HEADER_KEY, $instanceKey);
    }
}
