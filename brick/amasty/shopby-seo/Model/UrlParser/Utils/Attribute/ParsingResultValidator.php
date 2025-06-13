<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Utils\Attribute;

use Amasty\ShopbySeo\Helper\Data;
use Amasty\ShopbySeo\Model\Source\OptionSeparator;
use Amasty\ShopbySeo\Model\UrlParser\Utils\AliasesDelimiterProvider;

class ParsingResultValidator
{
    /**
     * @var Data
     */
    private $seoHelper;

    /**
     * @var AliasesDelimiterProvider
     */
    private $delimiterProvider;

    /**
     * @var OptionSeparator
     */
    private $optionSeparator;

    public function __construct(
        Data $seoHelper,
        AliasesDelimiterProvider $delimiterProvider,
        OptionSeparator $optionSeparator
    ) {
        $this->seoHelper = $seoHelper;
        $this->delimiterProvider = $delimiterProvider;
        $this->optionSeparator = $optionSeparator;
    }

    /**
     * Runs parsing results validation
     *
     * @param string $seoPart
     * @param array $parsedAttributeCodes
     * @param array $parsedAliases
     * @return bool
     */
    public function validate(string $seoPart, array $parsedAttributeCodes, array $parsedAliases): bool
    {
        $requestedAliases = $this->getRequestedAliases($seoPart, $parsedAttributeCodes);
        $parsedAliases = implode('', $parsedAliases);
        return str_replace($this->getCharsForReplace(), '', $parsedAliases) === $requestedAliases;
    }

    private function getRequestedAliases(string $seoPart, array $attributeCodes): string
    {
        $attributeCodes = array_map(function ($attributeCode) {
            return '/' . $attributeCode . '/';
        }, $attributeCodes);

        $seoPartWithoutAttribute = preg_replace($attributeCodes, '', $seoPart, 1);
        $seoPart = $seoPartWithoutAttribute ?: $seoPart;

        return str_replace($this->getCharsForReplace(), '', $seoPart);
    }

    private function getCharsForReplace(): array
    {
        $chars = $this->optionSeparator->toArray();

        return array_values($chars);
    }
}
