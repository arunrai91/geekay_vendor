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

/**
 * @deprecated behavior when separator and delimiter were the same, no longer stable
 * @see \Amasty\ShopbySeo\Model\UrlParser\Utils\Attribute\DefaultAliasParser
 */
class OldAliasParser implements ParserInterface
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

    public function __construct(
        SeoOptions $seoOptions,
        ParamsUpdater $paramsUpdater,
        ParsingResultValidator $resultValidator
    ) {
        $this->seoOptions = $seoOptions;
        $this->paramsUpdater = $paramsUpdater;
        $this->resultValidator = $resultValidator;
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
        $attributeOptionsData = $this->seoOptions->getData();
        $params = [];
        $parsedAliases = [];
        $parsedAttributeCodes = [];
        $currentAttributeCode = '';
        foreach ($aliases as $groupKey => $aliasGroup) {
            foreach ($aliasGroup as $key => $currentAlias) {
                if (array_key_exists($currentAlias, $attributeOptionsData)) {
                    $currentAttributeCode = $currentAlias;
                    $parsedAttributeCodes[] = $currentAlias;
                    continue 2;
                }

                if ($currentAttributeCode) {
                    foreach ($attributeOptionsData[$currentAttributeCode] as $optionId => $alias) {
                        if ($alias === $currentAlias) {
                            $parsedAliases[] = $currentAlias;
                            $this->paramsUpdater->update($params, $currentAttributeCode, (string) $optionId);
                            continue 3;
                        }
                    }
                }
            }
        }

        return $this->resultValidator->validate($seoPart, $parsedAttributeCodes, $parsedAliases) ? $params : [];
    }
}
