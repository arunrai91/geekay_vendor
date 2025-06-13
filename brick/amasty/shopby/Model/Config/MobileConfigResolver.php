<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Config;

use Amasty\Shopby\Model\ConfigProvider;
use Amasty\ShopbyBase\Model\Detection\MobileDetect;

class MobileConfigResolver
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var MobileDetect
     */
    private $mobileDetect;

    public function __construct(ConfigProvider $configProvider, MobileDetect $mobileDetect)
    {
        $this->configProvider = $configProvider;
        $this->mobileDetect = $mobileDetect;
    }

    public function isAjaxEnabled(): bool
    {
        return $this->configProvider->isAjaxEnabled()
            || $this->getSubmitFilterMode();
    }

    /**
     * @see \Amasty\Shopby\Model\Source\SubmitMode
     */
    public function getSubmitFilterMode(): int
    {
        if ($this->mobileDetect->isMobile()) {
            return $this->configProvider->getSubmitFiltersMobile();
        }

        return $this->configProvider->getSubmitFiltersDesktop();
    }
}
