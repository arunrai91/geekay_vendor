<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\LicenceService\Api\Client;

use Amasty\Base\Model\LicenceService\Request\Url\Builder;
use Amasty\Base\Model\SimpleDataObject;
use Amasty\Base\Model\SysInfo\RegisteredInstanceRepository;
use Amasty\Base\Utils\Http\Curl;
use Amasty\Base\Utils\Http\CurlFactory;
use Laminas\Http\Request;

class AdditionalInfo
{
    public const INFO_URL = '/api/v1/instance_client/info';
    private const HTTP_OK = 200;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var RegisteredInstanceRepository
     */
    private $registeredInstanceRepository;

    /**
     * @var Builder
     */
    private $urlBuilder;

    public function __construct(
        CurlFactory $curlFactory,
        RegisteredInstanceRepository $registeredInstanceRepository,
        Builder $urlBuilder
    ) {
        $this->curlFactory = $curlFactory;
        $this->registeredInstanceRepository = $registeredInstanceRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string[]
     */
    public function requestAdditionalInfo(array $params): SimpleDataObject
    {
        $curl = $this->createCurl();
        $response = $curl->request($this->buildInfoUrl($params), '{}', Request::METHOD_GET);

        if ($response->getData('code') !== self::HTTP_OK) {
            return $this->generateEmptyResponse();
        }

        return $response;
    }

    private function createCurl(): Curl
    {
        $curl = $this->curlFactory->create();
        $curl->setHeaders([
            'Accept: application/json',
            'Content-Type: application/json'
        ]);

        return $curl;
    }

    private function buildInfoUrl(array $params): string
    {
        $url = self::INFO_URL;
        if ($systemInstanceKey = $this->getSystemInstanceKey()) {
            $url .= '/' . $systemInstanceKey;
        }

        return $this->urlBuilder->build($url, $params);
    }

    private function getSystemInstanceKey(): ?string
    {
        $registeredInstance = $this->registeredInstanceRepository->get();

        return $registeredInstance->getCurrentInstance()
            ? $registeredInstance->getCurrentInstance()->getSystemInstanceKey()
            : null;
    }

    /**
     * @return string[]
     */
    private function generateEmptyResponse(): array
    {
        return [''];
    }
}
