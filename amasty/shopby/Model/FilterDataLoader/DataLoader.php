<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\FilterDataLoader;

use Amasty\Shopby\Model\Layer\Filter\IsNew;
use Amasty\Shopby\Model\Layer\Filter\OnSale;
use Amasty\Shopby\Model\Layer\Filter\Rating;
use Amasty\Shopby\Model\Layer\Filter\Stock;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\FilterDataLoader\AdapterInterface;

class DataLoader implements AdapterInterface
{
    public const CUSTOM_FILTERS = [
        OnSale::ATTRIBUTE_CODE,
        IsNew::ATTRIBUTE_CODE,
        Rating::REQUEST_VAR,
        Stock::REQUEST_VAR
    ];

    /**
     * @var ConfigReader
     */
    private $configReader;

    public function __construct(ConfigReader $configReader)
    {
        $this->configReader = $configReader;
    }

    public function load(FilterSettingInterface $filterSetting, string $filterCode, ?string $fieldName = null): void
    {
        $filterSetting->setId($filterCode);
        $filterSetting->setAttributeCode($filterCode);
        $filterSetting->setIsExpanded($this->configReader->getExpandValue($filterCode));
        $filterSetting->setTooltip($this->configReader->getTooltip($filterCode));
        $filterSetting->setBlockPosition($this->configReader->getBlockPosition($filterCode));
        $filterSetting->setTopPosition($this->configReader->getPositionTop($filterCode));
        $filterSetting->setSidePosition($this->configReader->getPositionSide($filterCode));
        $filterSetting->setPosition($this->configReader->getPosition($filterCode));
    }

    public function isApplicable(string $filterCode): bool
    {
        return in_array($filterCode, self::CUSTOM_FILTERS);
    }
}
