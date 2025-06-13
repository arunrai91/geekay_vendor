<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\SyncService;

use Amasty\Base\Model\LicenceService\Request\Data\InstanceInfo\Domain as RequestDomain;
use Amasty\Base\Model\SysInfo\Provider\Collector\LicenceService\Domain;
use Amasty\Base\Model\SysInfo\RegisteredInstanceRepository;
use Amasty\Geoip\Api\Data\BlockInterface;
use Amasty\Geoip\Api\Data\BlockV6Interface;
use Amasty\Geoip\Api\Data\IpLogInterface;
use Amasty\Geoip\Exceptions\LicenseInvalidException;
use Amasty\Geoip\Model\BlockFactory;
use Amasty\Geoip\Model\BlockV6Factory;
use Amasty\Geoip\Model\LocationFactory;
use Amasty\Geoip\Model\SyncService\Headers\AddInstanceIdHeader;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;

class Client
{
    public const BLOCK_RESPONSE_KEY = 'amasty_geoip_block';
    public const BLOCK_V6_RESPONSE_KEY = 'amasty_geoip_block_v6';
    public const LOCATION_RESPONSE_KEY = 'amasty_geoip_location';
    private const SYNC_URL = 'https://magento-geoip.saas.amasty.com/api/geoip/table_data';

    /**
     * @var Curl
     */
    private $httpClient;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var BlockV6Factory
     */
    private $blockV6Factory;

    /**
     * @var LocationFactory
     */
    private $locationFactory;

    /**
     * @var RegisteredInstanceRepository
     */
    private $registeredInstanceRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Domain
     */
    private $domainCollector;

    /**
     * @var string
     */
    private $shopDomain = '';

    /**
     * @var AddInstanceIdHeader
     */
    private $addInstanceIdHeader;

    public function __construct(
        Curl $curl,
        BlockFactory $blockFactory,
        BlockV6Factory $blockV6Factory,
        LocationFactory $locationFactory,
        RegisteredInstanceRepository $registeredInstanceRepository,
        SerializerInterface $serializer,
        ?Domain $domainCollector = null, // TODO move to not optional
        ?AddInstanceIdHeader $addInstanceIdHeader = null // TODO move to not optional
    ) {
        $this->httpClient = $curl;
        $this->blockFactory = $blockFactory;
        $this->blockV6Factory = $blockV6Factory;
        $this->locationFactory = $locationFactory;
        $this->registeredInstanceRepository = $registeredInstanceRepository;
        $this->serializer = $serializer;
        $this->domainCollector = $domainCollector ?? ObjectManager::getInstance()->get(Domain::class);
        $this->addInstanceIdHeader = $addInstanceIdHeader
            ?? ObjectManager::getInstance()->get(AddInstanceIdHeader::class);
    }

    /**
     * @param BlockInterface[]|BlockV6Interface[] $blocks
     * @return array
     * @throws LocalizedException
     */
    public function requestDataToInsert(array $blocks): array
    {
        if (empty($blocks)) {
            return $this->getEmptyResponse();
        }

        $this->httpClient->setHeaders([
            'Accept: application/json',
            'Content-Type: application/json'
        ]);
        $this->addInstanceIdHeader->add($this->httpClient);

        $this->httpClient->post(self::SYNC_URL, $this->prepareRequestBody($blocks));

        if ($this->httpClient->getStatus() >= 400) {
            if ($this->httpClient->getStatus() < 500) {
                throw new LicenseInvalidException();
            }

            throw new LocalizedException($this->getClientMessage());
        }

        $response = $this->httpClient->getBody();

        return $this->convertResponse($this->serializer->unserialize($response));
    }

    public function _resetState(): void
    {
        $this->shopDomain = null;
    }

    private function getClientMessage(): Phrase
    {
        $responseMessage = $this->serializer->unserialize($this->httpClient->getBody() ?: '{}')['message']
            ?? null;

        return null === $responseMessage
            ? __('GeoIP request failed with status code %1', $this->httpClient->getStatus())
            : __($responseMessage);
    }

    /**
     * @param BlockInterface[]|BlockV6Interface[] $blocks
     * @return array[]
     */
    private function prepareRequestBody(array $blocks): array
    {
        $ipRanges = [];

        foreach ($blocks as $block) {
            $ipRanges[] = [
                'start_ip_num' => $block->getStartIpNum(),
                'end_ip_num' => $block->getEndIpNum(),
                'ip_version' => $this->getIpVersion($block)
            ];
        }

        return [
            'data' => $ipRanges,
            'shop_domain' => $this->getShopDomain()
        ];
    }

    private function getEmptyResponse(): array
    {
        return [
            self::BLOCK_RESPONSE_KEY => [],
            self::BLOCK_V6_RESPONSE_KEY => [],
            self::LOCATION_RESPONSE_KEY => []
        ];
    }

    private function convertResponse(array $response): array
    {
        $responseData[self::BLOCK_RESPONSE_KEY] = $this->convertWithFactory(
            $this->blockFactory,
            $response[self::BLOCK_RESPONSE_KEY]
        );
        $responseData[self::BLOCK_V6_RESPONSE_KEY] = $this->convertWithFactory(
            $this->blockV6Factory,
            $response[self::BLOCK_V6_RESPONSE_KEY]
        );
        $responseData[self::LOCATION_RESPONSE_KEY] = $this->convertWithFactory(
            $this->locationFactory,
            $response[self::LOCATION_RESPONSE_KEY]
        );

        return $responseData;
    }

    private function convertWithFactory(object $factoryClass, array $data)
    {
        return array_map(static function ($itemData) use ($factoryClass) {
            $item = $factoryClass->create();
            $item->addData($itemData);

            return $item;
        }, $data);
    }

    /**
     * @param BlockInterface|BlockV6Interface $block
     * @return void
     */
    private function getIpVersion($block): int
    {
        return $block instanceof BlockInterface
            ? IpLogInterface::IP_V_4
            : IpLogInterface::IP_V_6;
    }

    private function getShopDomain(): string
    {
        if (empty($this->shopDomain)) {
            $registeredInstance = $this->registeredInstanceRepository->get()->getCurrentInstance();

            $this->shopDomain = (string)($registeredInstance
                ? $registeredInstance->getDomain()
                : $this->domainCollector->get()[0][RequestDomain::URL]);
        }

        return $this->shopDomain;
    }
}
