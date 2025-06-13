<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\SeoOptionsModifier;

use Amasty\ShopbySeo\Model\ConfigProvider;
use Magento\Catalog\Model\Product\Url as ProductUrl;

class UniqueBuilder
{
    public const DEFAULT_FORMAT = '-';

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Contains unique url value
     * @var array
     */
    private $urlUniqueCounter = [];

    /**
     * @var ProductUrl
     */
    private $productUrl;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ProductUrl $productUrl,
        ?ConfigProvider $configProvider
    ) {
        $this->productUrl = $productUrl;
        $this->configProvider = $configProvider;
    }

    /**
     * @param string $value
     * @param string|int $optionId can be category id with static string prefix
     * @param bool $forceUniqueValue
     */
    public function execute(string $value, $optionId = 0, bool $forceUniqueValue = false): string
    {
        if (isset($this->cache[$optionId])) {
            return (string)$this->cache[$optionId];
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $value = html_entity_decode($value, ENT_QUOTES);
        $formattedValue = $this->productUrl->formatUrlKey($value) ?: self::DEFAULT_FORMAT;
        $specialChar = $this->configProvider->getSpecialChar();
        $formattedValue = str_replace('-', $specialChar, $formattedValue);

        $unique = $formattedValue;

        if ($forceUniqueValue || !$this->configProvider->isIncludeAttributeName()) {
            $i = 0;
            while ($this->checkIfNotUnique($unique, $optionId)) {
                if (isset($this->urlUniqueCounter[$formattedValue])) {
                    $i = $this->urlUniqueCounter[$formattedValue];
                }
                $this->urlUniqueCounter[$formattedValue] = ++$i;
                $unique = $formattedValue . $specialChar . ($i);
            }
        }

        $this->cache[$optionId] = $unique;

        return (string)$unique;
    }

    /**
     * @param string $value
     * @param int|string $optionId
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkIfNotUnique(string $value, $optionId): bool
    {
        return in_array($value, $this->cache, true);
    }

    public function clear(): void
    {
        $this->cache = [];
        $this->urlUniqueCounter = [];
    }
}
