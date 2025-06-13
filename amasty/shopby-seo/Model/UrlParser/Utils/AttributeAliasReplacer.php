<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Utils;

use Amasty\ShopbySeo\Helper\Data;
use Amasty\ShopbySeo\Model\ConfigProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

class AttributeAliasReplacer
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $seoHelper;

    /**
     * @var SpecialCharReplacer
     */
    private $specialCharReplacer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        StoreManagerInterface $storeManager,
        Data $seoHelper,
        SpecialCharReplacer $specialCharReplacer = null, // TODO move to not optional
        ConfigProvider $configProvider = null // TODO move to not optional
    ) {
        $this->storeManager = $storeManager;
        $this->seoHelper = $seoHelper;
        // OM for backward compatibility
        $this->specialCharReplacer = $specialCharReplacer ?? ObjectManager::getInstance()
            ->get(SpecialCharReplacer::class);
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
    }

    /**
     * Replace all existed attribute aliases in seo part request string
     *
     * @param string $seoPart
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function replace(string $seoPart): string
    {
        $store = $this->storeManager->getStore()->getId();

        /* Need for preparing attributes url aliases */
        $this->seoHelper->getSeoSignificantAttributeCodes();

        $replaces = [];
        $isOldParser = $this->configProvider->getSpecialChar() === $this->configProvider->getOptionSeparator();

        foreach ($this->seoHelper->getAttributeUrlAliases() as $attributeCode => $alias) {
            if (!empty($alias[$store])) {
                if (!$isOldParser) {
                    $attributeCode = $this->specialCharReplacer->replace($attributeCode);
                }
                $attributeAliasPattern = sprintf(
                    '/%s/',
                    $alias[$store] . '\\' . $this->configProvider->getOptionSeparator()
                );

                $replaces[$attributeCode . $this->configProvider->getOptionSeparator()] = $attributeAliasPattern;
            }
        }

        $seoPart .= $this->configProvider->getOptionSeparator(); // add dummy separator in end for correct regexp work
        $seoPart = preg_replace(array_values($replaces), array_keys($replaces), $seoPart);
        $seoPart .= $this->configProvider->getOptionSeparator();
        $seoPart = rtrim($seoPart, $this->configProvider->getOptionSeparator()); // remove dummy separator

        return $seoPart;
    }
}
