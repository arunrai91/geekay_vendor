<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\SeoOptionsModifier;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbySeo\Helper\Data;

class HardcodedAliases
{
    /**
     * @var UniqueBuilder
     */
    private $uniqueBuilder;

    /**
     * @var \Amasty\ShopbyBase\Model\ResourceModel\OptionSetting
     */
    private $optionSettingResource;

    /**
     * @var \Amasty\ShopbySeo\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var Data
     */
    private $seoHelper;

    public function __construct(
        UniqueBuilder $uniqueBuilder,
        \Amasty\ShopbyBase\Model\ResourceModel\OptionSetting $optionSettingResource,
        \Amasty\ShopbySeo\Model\ConfigProvider $configProvider,
        Data $seoHelper
    ) {
        $this->uniqueBuilder = $uniqueBuilder;
        $this->optionSettingResource = $optionSettingResource;
        $this->configProvider = $configProvider;
        $this->seoHelper = $seoHelper;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function modify(array &$optionsSeoData, int $storeId, array &$attributeIds = []): void
    {
        $hardcodedAliases = $this->loadHardcodedAliases($storeId);
        foreach ($hardcodedAliases as $row) {
            $attributeCode = $row[OptionSettingInterface::ATTRIBUTE_CODE];

            $alias = $this->uniqueBuilder->execute(
                (string) $row[OptionSettingInterface::URL_ALIAS],
                (string) $row[OptionSettingInterface::VALUE]
            );
            $optionsSeoData[$storeId][$attributeCode][$row[OptionSettingInterface::VALUE]] = $alias;
        }
    }

    private function loadHardcodedAliases(int $storeId): array
    {
        $aliases = [];
        if ($this->configProvider->isSeoUrlEnabled($storeId)) {
            $aliases = $this->optionSettingResource->getHardcodedAliases(
                $storeId,
                $this->seoHelper->getSeoSignificantAttributeCodes()
            );
        }

        return $aliases;
    }
}
