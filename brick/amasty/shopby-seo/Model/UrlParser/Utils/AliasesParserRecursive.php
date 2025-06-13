<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Utils;

class AliasesParserRecursive
{
    /**
     * @var AliasesDelimiterProvider
     */
    private $delimiterProvider;

    public function __construct(AliasesDelimiterProvider $delimiterProvider)
    {
        $this->delimiterProvider = $delimiterProvider;
    }

    /**
     * Parsing seo part aliases recursively
     *
     * @param array $seoPart
     * @return array
     */
    public function execute(array $seoPart): array
    {
        $aliases = [];
        $aliasGroup = [];
        if (empty($seoPart)) {
            return $aliases;
        }

        for ($i = count($seoPart) - 1; $i >= 0; $i--) {
            $aliasGroup[] = implode(
                $this->delimiterProvider->execute(),
                array_slice($seoPart, 0, $i + 1)
            );
        }

        $aliases[] = $aliasGroup;
        array_shift($seoPart);

        return array_merge($aliases, $this->execute($seoPart));
    }
}
