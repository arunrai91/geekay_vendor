<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlRewrite;

use Magento\Store\Model\StoreManager;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Check for existing url rewrite.
 */
class IsExist
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var StoreManager
     */
    private $storeManager;

    public function __construct(
        UrlFinderInterface $urlFinder,
        StoreManager $storeManager
    ) {
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
    }

    public function execute(string $path, ?int $storeId = null, ?string $entityType = null, ?int $entityId = null): bool
    {
        $data = [
            UrlRewrite::REQUEST_PATH => $path,
            UrlRewrite::STORE_ID => $storeId ?? $this->storeManager->getStore()->getId(),
        ];

        if ($entityType !== null) {
            $data[UrlRewrite::ENTITY_TYPE] = $entityType;
        }
        if ($entityId !== null) {
            $data[UrlRewrite::ENTITY_ID] = $entityId;
        }

        $rewrite = $this->urlFinder->findOneByData($data);
        if ($rewrite === null) {
            return false;
        }

        return true;
    }
}
