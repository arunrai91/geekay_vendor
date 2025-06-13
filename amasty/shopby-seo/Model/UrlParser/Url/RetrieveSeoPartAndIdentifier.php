<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Url;

use Amasty\ShopbySeo\Model\ConfigProvider;

class RetrieveSeoPartAndIdentifier
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function execute(string $identifier): ?array
    {
        $filterWord = $this->configProvider->getFilterWord();
        if ($filterWord) {
            if (strpos($identifier, '/' . $filterWord . '/') !== false) {
                $filterWordPosition = strpos($identifier, '/' . $filterWord . '/');
                $seoPart = substr(
                    $identifier,
                    $filterWordPosition + strlen('/' . $filterWord . '/')
                );
                $identifier = substr($identifier, 0, $filterWordPosition);
            } else {
                return null;
            }
        } else {
            $lastSlashPosition = strrpos($identifier, '/');
            if ($lastSlashPosition !== false) {
                $seoPart = substr($identifier, $lastSlashPosition + 1);
                $identifier = substr($identifier, 0, $lastSlashPosition);
            } else {
                $seoPart = $identifier;
                $identifier = '';
            }
        }

        return [$seoPart, $identifier];
    }
}
