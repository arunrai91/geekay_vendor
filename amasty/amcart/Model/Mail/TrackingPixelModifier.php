<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\Mail;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class TrackingPixelModifier
{
    /**
     * @var Url
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Url $urlBuilder,
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->urlBuilder = $urlBuilder;
        // OM for backward compatibility
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    public function execute(string $hash, string $emailBody, ?int $storeId = null): string
    {
        // In case storeId = 0 we should avoid getting incorrect tracking url.
        if (null === $storeId || $storeId === Store::DEFAULT_STORE_ID) {
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }

        $trackingUrl = $this->urlBuilder->getUrl(
            'amasty_acart/email/open',
            ['uid' => $hash, '_scope' => $storeId]
        );
        $emailBody = str_replace(
            '</body>',
            '<img src="' . $trackingUrl . '" style="display: contents"></body>',
            $emailBody
        );

        return $emailBody;
    }
}
