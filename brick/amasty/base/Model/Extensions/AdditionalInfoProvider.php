<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Extensions;

use Amasty\Base\Model\LicenceService\Api\RequestFacade;
use Amasty\Base\Model\Serializer;
use Amasty\Base\Model\SysInfo\Provider\Collector;
use Amasty\Base\Utils\XssStringEscaper;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config;
use Magento\Framework\Exception\NotFoundException;

class AdditionalInfoProvider
{
    private const CACHE_IDENTIFIER = 'ambase_additional_info_block';
    private const CACHE_TTL = 86400; // one day

    public const ADDITIONAL_INFO_REQUEST_GROUP = 'additionalInfoRequest';

    /**
     * @var RequestFacade
     */
    private $client;

    /**
     * @var XssStringEscaper
     */
    private $xssEscaper;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Collector
     */
    private $collector;

    /**
     * @var SimpleDataObjectConverter
     */
    private $simpleDataObjectConverter;

    public function __construct(
        RequestFacade $client,
        XssStringEscaper $xssEscaper,
        CacheInterface $cache,
        Serializer $serializer,
        Collector $collector,
        SimpleDataObjectConverter $simpleDataObjectConverter
    ) {
        $this->client = $client;
        $this->xssEscaper = $xssEscaper;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->collector = $collector;
        $this->simpleDataObjectConverter = $simpleDataObjectConverter;
    }

    public function get(): array
    {
        if ($cachedResponse = $this->cache->load(self::CACHE_IDENTIFIER)) {
            return $this->serializer->unserialize($cachedResponse);
        }

        try {
            $data = $this->collector->collect(self::ADDITIONAL_INFO_REQUEST_GROUP);
        } catch (NotFoundException $e) {
            $data = [];
        }

        $response = $this->simpleDataObjectConverter
            ->convertKeysToCamelCase($this->client->getAdditionalInfo($data)->toArray());

        $result = array_map(
            [$this, 'escapeData'],
            $response
        );
        $this->saveToCache($result);

        return $result;
    }

    /**
     * @param string|array $data
     * @return string
     */
    private function escapeData($data): string
    {
        return is_scalar($data)
            ? $this->xssEscaper->escapeScriptInHtml((string)$data)
            : '';
    }

    private function saveToCache(array $data): void
    {
        $this->cache->save(
            $this->serializer->serialize($data),
            self::CACHE_IDENTIFIER,
            [Config::CACHE_TAG],
            self::CACHE_TTL
        );
    }
}
