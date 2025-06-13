<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\System\Config\InformationBlocks\Validation;

use Magento\Framework\Module\Manager;

class ShopByLiveSearch implements MessageValidatorInterface
{
    public const MAGENTO_LIVE_SEARCH_MODULE = 'Magento_LiveSearch';

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $amShopbyLiveSearchModule;

    public function __construct(
        Manager $moduleManager,
        string $amShopbyLiveSearchModule = 'Amasty_ShopByLiveSearch'
    ) {
        $this->moduleManager = $moduleManager;
        $this->amShopbyLiveSearchModule = $amShopbyLiveSearchModule;
    }

    public function isValid(): bool
    {
        return $this->isMagentoLiveSearchEnable() && !$this->isShopByLiveSearchEnabled();
    }

    private function isShopByLiveSearchEnabled(): bool
    {
        return $this->moduleManager->isEnabled($this->amShopbyLiveSearchModule);
    }

    private function isMagentoLiveSearchEnable(): bool
    {
        return $this->moduleManager->isEnabled(self::MAGENTO_LIVE_SEARCH_MODULE);
    }
}
