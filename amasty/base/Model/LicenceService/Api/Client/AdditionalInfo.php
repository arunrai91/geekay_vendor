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
use Amasty\Base\Model\SimpleDataObjectFactory;
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

    /**
     * @var SimpleDataObjectFactory
     */
    private $simpleDataObjectFactory;

    public function __construct(
        CurlFactory $curlFactory,
        RegisteredInstanceRepository $registeredInstanceRepository,
        Builder $urlBuilder,
        SimpleDataObjectFactory $simpleDataObjectFactory
    ) {
        $this->curlFactory = $curlFactory;
        $this->registeredInstanceRepository = $registeredInstanceRepository;
        $this->urlBuilder = $urlBuilder;
        $this->simpleDataObjectFactory = $simpleDataObjectFactory;
    }

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
     * @return SimpleDataObject
     */
    private function generateEmptyResponse(): SimpleDataObject
    {
        return $this->simpleDataObjectFactory->create();
    }
}
