<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\SyncService;

use Amasty\Geoip\Model\SyncService\Headers\AddInstanceIdHeader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class LicenseValidatorClient
{
    private const VALIDATION_URL = 'https://magento-geoip.saas.amasty.com/api/license/validate';

    /**
     * @var Curl
     */
    private $httpClient;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var AddInstanceIdHeader
     */
    private $addInstanceIdHeader;

    /**
     * @var Phrase
     */
    private $errorMessage;

    public function __construct(
        Curl $httpClient,
        AddInstanceIdHeader $addInstanceIdHeader,
        JsonSerializer $serializer
    ) {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->addInstanceIdHeader = $addInstanceIdHeader;
    }

    public function isValid(): bool
    {
        $this->httpClient->setHeaders([
            'Accept: application/json',
            'Content-Type: application/json'
        ]);
        try {
            $this->addInstanceIdHeader->add($this->httpClient);
        } catch (LocalizedException $e) {
            $this->errorMessage = __($e->getMessage());

            return false;
        }

        $this->httpClient->get(self::VALIDATION_URL);

        return $this->httpClient->getStatus() === 200;
    }

    public function getMessage(): Phrase
    {
        if ($this->errorMessage !== null) {
            return $this->errorMessage;
        }

        $defaultMsg = __('Couldn\'t validate license. Service is not available.');
        if ($this->httpClient->getStatus() >= 400 && $this->httpClient->getStatus() < 500) {
            return __('Invalid license key. To use Amasty Service, please check the license status.');
        }

        if ($this->httpClient->getStatus() >= 500) {
            $responseMessage = $this->serializer->unserialize($this->httpClient->getBody() ?: '{}')['message']
                ?? null;

            return null === $responseMessage
                ?  $defaultMsg
                : __($responseMessage);
        }

        return $defaultMsg;
    }
}
