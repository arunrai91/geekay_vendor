<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Url;

use Amasty\ShopbySeo\Model\ConfigProvider;
use Amasty\ShopbySeo\Model\UrlRewrite\IsExist as IsUrlRewriteExist;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;

class IsAllowed
{
    /**
     * @var array
     */
    private $skipIdentifiers = [
        'catalog/category/',
        'catalog/product/',
        'cms/page/',
        'amasty_xsearch/',
        'customer/',
        'checkout/',
        'catalogsearch',
        'stores/store',
        'amasty_fpc/reports/',
        'amcookie/cookie/cookies'
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var IsUrlRewriteExist
     */
    private $isUrlRewriteExist;

    /**
     * @var bool
     */
    private $skipIdentifiersUpdated = false;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        IsUrlRewriteExist $isUrlRewriteExist,
        Manager $moduleManager,
        ConfigProvider $configProvider,
        array $skipIdentifiers = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->isUrlRewriteExist = $isUrlRewriteExist;
        $this->moduleManager = $moduleManager;
        $this->configProvider = $configProvider;
        $this->skipIdentifiers = array_merge($this->skipIdentifiers, array_values($skipIdentifiers));
    }

    public function execute(string $identifier): bool
    {
        if (!empty($identifier)) {
            $this->updateSkipIdentifiers();
            foreach ($this->skipIdentifiers as $skipIdentifier) {
                if (strpos($identifier, $skipIdentifier) === 0) {
                    return false;
                }
            }

            return !$this->isUrlRewriteExist->execute($identifier);
        }

        return false;
    }

    private function updateSkipIdentifiers(): void
    {
        if ($this->skipIdentifiersUpdated) {
            return;
        }

        if ($this->getConfigValue('amasty_xsearch/general/enable_seo_url')
        && $this->moduleManager->isEnabled('Amasty_Xsearch')) {
            $this->skipIdentifiers[] = $this->getConfigValue('amasty_xsearch/general/seo_key');
        }

        $this->skipIdentifiers = array_merge($this->skipIdentifiers, $this->configProvider->getIgnoredUrls());

        $this->skipIdentifiersUpdated = true;
    }

    /**
     * @return mixed
     */
    private function getConfigValue(string $path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}
