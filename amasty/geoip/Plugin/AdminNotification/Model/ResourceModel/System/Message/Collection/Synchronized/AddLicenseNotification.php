<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Plugin\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;

use Amasty\Geoip\Model\System\Message\LicenseInvalid as LicenseInvalidMessage;
use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;
use Magento\Framework\AuthorizationInterface;

class AddLicenseNotification
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var LicenseInvalidMessage
     */
    private $licenseInvalidMessage;

    public function __construct(
        AuthorizationInterface $authorization,
        LicenseInvalidMessage $licenseInvalidMessage
    ) {
        $this->authorization = $authorization;
        $this->licenseInvalidMessage = $licenseInvalidMessage;
    }

    /**
     * @param Synchronized $collection
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToArray(
        Synchronized $collection,
        array $result
    ): array {
        if ($this->shouldAddLicenseNotification($result)) {
            $result['items'][] = $this->licenseInvalidMessage->toArray();
            $result['totalRecords'] = ++$result['totalRecords'];
        }

        return $result;
    }

    private function shouldAddLicenseNotification(array $result): bool
    {
        return isset($result['items'])
            && isset($result['totalRecords'])
            && $this->authorization->isAllowed('Amasty_Geoip::amasty_geoip')
            && $this->licenseInvalidMessage->isDisplayed();
    }
}
