<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser;

use Amasty\ShopbySeo\Helper\Data;
use Amasty\ShopbySeo\Model\UrlParser\Utils\AliasesDelimiterProvider;
use Amasty\ShopbySeo\Model\UrlParser\Utils\AliasesParserRecursive;
use Amasty\ShopbySeo\Model\UrlParser\Utils\Attribute\DefaultAliasParser;
use Amasty\ShopbySeo\Model\UrlParser\Utils\Attribute\OldAliasParser;
use Amasty\ShopbySeo\Model\UrlParser\Utils\AttributeAliasReplacer;
use Magento\Framework\Exception\NoSuchEntityException;

class Attribute
{
    /**
     * @var  Data
     */
    protected $seoHelper;

    /**
     * @var string
     */
    protected $aliasDelimiter;

    /**
     * @var AliasesParserRecursive
     */
    private $parserRecursive;

    /**
     * @var AliasesDelimiterProvider
     */
    private $delimiterProvider;

    /**
     * @var AttributeAliasReplacer
     */
    private $attributeAliasReplacer;

    /**
     * @var DefaultAliasParser
     */
    private $defaultAliasParser;

    /**
     * @var OldAliasParser
     */
    private $oldAliasParser;

    public function __construct(
        Data $seoHelper,
        AliasesParserRecursive $parserRecursive,
        AliasesDelimiterProvider $delimiterProvider,
        AttributeAliasReplacer $attributeAliasReplacer,
        DefaultAliasParser $defaultAliasParser,
        OldAliasParser $oldAliasParser
    ) {
        $this->seoHelper = $seoHelper;
        $this->parserRecursive = $parserRecursive;
        $this->delimiterProvider = $delimiterProvider;
        $this->attributeAliasReplacer = $attributeAliasReplacer;
        $this->defaultAliasParser = $defaultAliasParser;
        $this->oldAliasParser = $oldAliasParser;
    }

    /**
     * Parse seo part string request depends on attributes
     *
     * @param string $seoPart
     * @return array
     * @throws NoSuchEntityException
     */
    public function parse(string $seoPart): array
    {
        $seoPart = $this->attributeAliasReplacer->replace($seoPart);
        $aliases = explode($this->delimiterProvider->execute(), $seoPart);

        if ($this->delimiterProvider->execute() == $this->seoHelper->getSpecialChar()) {
            $aliases = $this->parserRecursive->execute($aliases);
            return $this->oldAliasParser->parse($aliases, $seoPart);
        }

        return $this->defaultAliasParser->parse($aliases, $seoPart);
    }
}
