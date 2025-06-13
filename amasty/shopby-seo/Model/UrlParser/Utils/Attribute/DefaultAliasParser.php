<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Utils\Attribute;

use Amasty\ShopbySeo\Model\SeoOptions;
use Amasty\ShopbySeo\Model\UrlParser\Utils\ParamsUpdater;
use Amasty\ShopbySeo\Model\UrlParser\Utils\SpecialCharReplacer;

class DefaultAliasParser implements ParserInterface
{
    /**
     * @var SeoOptions
     */
    private $seoOptions;

    /**
     * @var ParamsUpdater
     */
    private $paramsUpdater;

    /**
     * @var ParsingResultValidator
     */
    private $resultValidator;

    /**
     * @var SpecialCharReplacer
     */
    private $specialCharReplacer;

    public function __construct(
        SeoOptions $seoOptions,
        ParamsUpdater $paramsUpdater,
        ParsingResultValidator $resultValidator,
        SpecialCharReplacer $specialCharReplacer
    ) {
        $this->seoOptions = $seoOptions;
        $this->paramsUpdater = $paramsUpdater;
        $this->resultValidator = $resultValidator;
        $this->specialCharReplacer = $specialCharReplacer;
    }

    /**
     * Parse prepared aliases and update request
     *
     * @param array $aliases
     * @param string $seoPart
     * @return array
     */
    public function parse(array $aliases, string $seoPart): array
    {
        $attributeOptionsData = [];
        foreach ($this->seoOptions->getData() as $attributeCode => $optionsData) {
            $attributeOptionsData[$this->specialCharReplacer->replace($attributeCode)] = $optionsData;
        }

        $parsedAttributes = [];
        $parsedAliases = [];
        $params = [];
        $currentAttributeCode = '';
        foreach ($aliases as $currentAlias) {
            if ($currentAttributeCode
                && (!isset($parsedAttributes[$currentAttributeCode])
                    || !in_array($currentAlias, $parsedAttributes[$currentAttributeCode], true))
            ) {
                $optionsData = $attributeOptionsData[$currentAttributeCode];
                $optionId = array_search($currentAlias, $optionsData, true);
                if ($optionId !== false) {
                    $parsedAttributes[$currentAttributeCode][] = $currentAlias;
                    $parsedAliases[] = $currentAlias;
                    $this->paramsUpdater->update(
                        $params,
                        $this->specialCharReplacer->normalizeAttributeCode($currentAttributeCode),
                        (string)$optionId
                    );
                    continue;
                }
            }

            if (array_key_exists($currentAlias, $attributeOptionsData)) {
                $currentAttributeCode = $currentAlias;
            }
        }

        return $this->resultValidator->validate($seoPart, array_keys($parsedAttributes), $parsedAliases) ? $params : [];
    }
}
