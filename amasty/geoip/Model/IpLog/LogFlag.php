<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\IpLog;

use Magento\Customer\Model\Session as CustomerSession;

class LogFlag
{
    public const FLAG_NAME = 'amasty_geoip_ip_logged';

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    public function isLogged(): bool
    {
        return $this->customerSession->getData(self::FLAG_NAME) === true;
    }

    public function setIsLogged(): void
    {
        $this->customerSession->setData(self::FLAG_NAME, true);
    }
}
