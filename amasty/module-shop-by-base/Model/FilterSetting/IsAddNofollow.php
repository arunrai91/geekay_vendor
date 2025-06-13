<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\FilterSetting;

use Amasty\Shopby\Model\Layer\GetSelectedFiltersSettings as SelectedFiltersSettings;
use Amasty\ShopbyBase\Model\Integration\Shopby\GetSelectedFiltersSettings;
use Amasty\ShopbyBase\Model\Integration\Shopby\IsBrandPage;
use Amasty\ShopbyBase\Model\Integration\ShopbySeo\GetConfigProvider;
use Amasty\ShopbySeo\Helper\Meta;
use Amasty\ShopbySeo\Model\ConfigProvider;
use Amasty\ShopbySeo\Model\Source\IndexMode;
use Magento\Framework\Registry;

class IsAddNofollow
{
    /**
     * @var ConfigProvider|null
     */
    private $seoConfigProvider;

    /**
     * @var SelectedFiltersSettings|null
     */
    private $selectedFiltersSettings;

    /**
     * @var IsBrandPage
     */
    private $isBrandPage;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        GetConfigProvider $getConfigProvider,
        GetSelectedFiltersSettings $getSelectedFiltersSettings,
        IsBrandPage $isBrandPage,
        Registry $registry
    ) {
        $this->seoConfigProvider = $getConfigProvider->execute();
        $this->selectedFiltersSettings = $getSelectedFiltersSettings->execute();
        $this->isBrandPage = $isBrandPage;
        $this->registry = $registry;
    }

    public function execute(int $relNofollow, int $followMode): bool
    {
        if (!$this->seoConfigProvider || !$relNofollow || !$this->seoConfigProvider->isEnableRelNofollow()) {
            return false;
        }

        if ($this->isPageNofollow()) {
            $result = true;
        } else {
            $result = $this->isNofollowByMode($followMode);
        }

        return $result;
    }

    private function isPageNofollow(): bool
    {
        return strpos((string) $this->registry->registry(Meta::AMSHOPBYSEO_FOLLOW), 'nofollow') !== false;
    }

    private function isNofollowByMode(int $followMode): bool
    {
        switch ($followMode) {
            case IndexMode::MODE_NEVER:
                $result = true;
                break;
            case IndexMode::MODE_SINGLE_ONLY:
                $result = $this->isNofollowBySingleMode();
                break;
            default:
                $result = false;
                break;
        }

        return $result;
    }

    private function isNofollowBySingleMode(): bool
    {
        return $this->selectedFiltersSettings && $this->isBrandPage && $this->isFiltersApply();
    }

    private function isFiltersApply(): bool
    {
        $count = count($this->selectedFiltersSettings->execute());

        if ($this->isBrandPage->execute()) {
            --$count;
        }

        return (bool) $count;
    }
}
