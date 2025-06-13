<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\SectionsData;

use Amasty\Base\Model\Serializer;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Psr\Log\LoggerInterface;

class FlushSections implements FlushSectionsInterface
{
    private const CACHE_SESSID_COOKIE_NAME = 'mage-cache-sessid';

    private const SECTION_DATA_IDS_COOKIE_NAME = 'section_data_ids';

    private const SECTION_DATA_IDS_DEFAULT_VALUE = '{}';

    private const SECTION_INVALIDATION_TIME = 1000;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Serializer $serializer,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->serializer = $serializer;
    }

    public function execute(Session $checkoutSession, array $sectionNames): void
    {
        try {
            if ($this->cookieManager->getCookie(self::CACHE_SESSID_COOKIE_NAME)) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie(self::CACHE_SESSID_COOKIE_NAME, $metadata);
            }

            $metadata = $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setPath($checkoutSession->getCookiePath());
            $sectionDataIds = $this->serializer->unserialize(
                $this->cookieManager->getCookie(
                    self::SECTION_DATA_IDS_COOKIE_NAME,
                    self::SECTION_DATA_IDS_DEFAULT_VALUE
                ),
                true
            );

            foreach ($sectionNames as $sectionName) {
                $sectionDataIds[$sectionName] = isset($sectionDataIds[$sectionName])
                    ? $sectionDataIds[$sectionName] + self::SECTION_INVALIDATION_TIME
                    : self::SECTION_INVALIDATION_TIME;
            }

            $this->cookieManager->deleteCookie(self::SECTION_DATA_IDS_COOKIE_NAME);
            $this->cookieManager->setPublicCookie(
                self::SECTION_DATA_IDS_COOKIE_NAME,
                $this->serializer->serialize($sectionDataIds),
                $metadata
            );
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }
    }
}
