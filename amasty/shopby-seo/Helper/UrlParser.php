<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Helper;

use Amasty\ShopbySeo\Helper\Url as UrlHelper;
use Amasty\ShopbySeo\Model\SeoOptions;
use Amasty\ShopbySeo\Model\UrlParser\Attribute;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class UrlParser extends AbstractHelper
{
    /**
     * @var  Data
     */
    private $seoHelper;

    /**
     * @var string
     */
    private $aliasDelimiter;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SeoOptions
     */
    private $seoOptions;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Attribute
     */
    private $attribute;

    public function __construct(
        Context $context,
        Data $seoHelper,
        Config $config,
        SeoOptions $seoOptions,
        StoreManagerInterface $storeManager,
        Attribute $attribute
    ) {
        parent::__construct($context);
        $this->seoHelper = $seoHelper;
        $this->config = $config;
        $this->aliasDelimiter = $this->config->getOptionSeparator() ?: '-';
        $this->seoOptions = $seoOptions;
        $this->attribute = $attribute;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $seoPart
     * @return array
     */
    public function parseSeoPart($seoPart)
    {
        $seoPart = str_replace('/', $this->aliasDelimiter, $seoPart);
        if ($this->seoHelper->isIncludeAttributeName()) {
            return $this->attribute->parse($seoPart);
        }

        if ($this->aliasDelimiter == $this->seoHelper->getSpecialChar()) {
            $aliases = $this->parseAliasesRecursive($seoPart);
            return $this->parseAliasesOldAlgorythm($aliases, $seoPart);
        }

        $aliases = $this->getAliases($seoPart);
        if ($aliases) {
            $aliases = array_filter($aliases);
        }
        return $this->parseAliases($aliases);
    }

    /**
     * @param $seoPart
     * @return array
     */
    public function getAliases($seoPart)
    {
        $aliases = explode($this->aliasDelimiter, $seoPart);
        return $aliases;
    }

    /**
     * @param $seoPart
     * @return array
     */
    private function parseAliasesRecursive($seoPart)
    {
        if (!is_array($seoPart)) {
            $seoPart = explode($this->aliasDelimiter, $seoPart);
        }

        $aliases = [];
        $aliasGroup = [];
        if (empty($seoPart)) {
            return $aliases;
        }

        for ($i = count($seoPart) - 1; $i >= 0; $i--) {
            $aliasGroup[] = implode($this->aliasDelimiter, array_slice($seoPart, 0, $i + 1));
        }

        $aliases[] = $aliasGroup;
        array_shift($seoPart);
        return array_merge($aliases, $this->parseAliasesRecursive($seoPart));
    }

    private function replaceAliases(array $aliases): array
    {
        $store = $this->storeManager->getStore()->getId();
        foreach ($this->seoHelper->getAttributeUrlAliases() as $attribute => $alias) {
            if (isset($alias[$store])) {
                $key = array_search($alias[$store], $aliases);
                if ($key !== false) {
                    $aliases[$key] = $attribute;
                }
            }
        }

        return $aliases;
    }

    /**
     * @param array $aliases
     * @return array
     */
    private function parseAliases($aliases)
    {
        $attributeOptionsData = $this->seoOptions->getData();
        $filterWord = $this->seoHelper->getFilterWord();
        $aliases = $this->replaceAliases($aliases);
        if (reset($aliases) === $filterWord) {
            array_shift($aliases);
        }
        $params = [];

        $parsedAliases = [];
        foreach ($aliases as $currentAlias) {
            foreach ($attributeOptionsData as $attributeCode => $optionsData) {
                foreach ($optionsData as $optionId => $alias) {
                    if ($alias === $currentAlias) {
                        $parsedAliases[] = $currentAlias;
                        $params = $this->addParsedOptionToParams($optionId, $attributeCode, $params);
                        continue 3;
                    }
                }
            }
        }

        foreach ($aliases as $key => $alias) {
            if ($alias === UrlHelper::CATEGORY_FILTER_PARAM) {
                unset($aliases[$key]);
                break;
            }
        }

        return implode($this->aliasDelimiter, $aliases) === implode($this->aliasDelimiter, $parsedAliases)
            ? $params : [];
    }

    /**
     * @param array $aliases
     * @param string $seoPart
     * @return array
     */
    private function parseAliasesOldAlgorythm($aliases, $seoPart)
    {
        $attributeOptionsData = $this->seoOptions->getData();
        $filterWord = $this->seoHelper->getFilterWord();
        $paramsCount = 0;
        $params = [];
        $parsedAliases = [];
        foreach ($aliases as $groupKey => $aliasGroup) {
            foreach ($aliasGroup as $key => $currentAlias) {
                foreach ($attributeOptionsData as $attributeCode => $optionsData) {
                    foreach ($optionsData as $optionId => $alias) {
                        if ($alias === $currentAlias) {
                            $parsedAliases[] = $currentAlias;
                            $params = $this->addParsedOptionToParams($optionId, $attributeCode, $params);
                            $paramsCount++;
                            continue 4;
                        }
                    }
                }

            }
        }

        $requestedAliases = $this->replaceAliases(explode($this->aliasDelimiter, $seoPart));
        $includeAttributeName = $this->seoHelper->isIncludeAttributeName();
        foreach ($requestedAliases as $key => $alias) {
            if ($alias == $filterWord
                || $alias === UrlHelper::CATEGORY_FILTER_PARAM
                || ($includeAttributeName && in_array($alias, array_keys($attributeOptionsData)))
            ) {
                unset($requestedAliases[$key]);
                continue;
            }
        }

        return implode($this->aliasDelimiter, $requestedAliases) == implode($this->aliasDelimiter, $parsedAliases)
            ? $params : [];
    }

    /**
     * @param $value
     * @param $paramName
     * @param $params
     * @return mixed
     */
    private function addParsedOptionToParams($value, $paramName, $params)
    {
        if (array_key_exists($paramName, $params)) {
            $params[$paramName] .= ',' . $value;
        } else {
            $params[$paramName] = '' . $value;
        }

        return $params;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function checkSeoParams(array $params = [])
    {
        $attributeOptionsData = $this->seoOptions->getData();
        foreach ($params as $paramName => $paramValue) {
            if (isset($attributeOptionsData[$paramName])
                && (!is_array($attributeOptionsData[$paramName])
                    || in_array($paramValue, $attributeOptionsData[$paramName]))
            ) {
                return true;
            }
        }

        return false;
    }
}
