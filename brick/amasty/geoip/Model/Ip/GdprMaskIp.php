<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\Ip;

class GdprMaskIp
{
    public function execute(string $ip): string
    {
        return $this->mask($ip);
    }

    private function mask(string $ip): string
    {
        $separator = $this->getSeparator($ip);

        $addressParts = explode($separator, $ip);
        array_pop($addressParts);
        $addressParts[] = '0'; // Mask IP according to EU GDPR law

        return implode($separator, $addressParts);
    }

    private function getSeparator(string $ip): string
    {
        return (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            ? ':'
            : '.';
    }
}
