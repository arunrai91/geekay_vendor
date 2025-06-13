<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\ShopbySeo\Controller\Router;

use Amasty\ShopbyBrand\Model\UrlParser\MatchBrandParams;
use Amasty\ShopbySeo\Controller\Router as SeoRouter;

class CheckForBrandUrl
{
    /**
     * @var MatchBrandParams
     */
    private $matchBrandParams;

    public function __construct(MatchBrandParams $matchBrandParams)
    {
        $this->matchBrandParams = $matchBrandParams;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSkipIdentifier(
        SeoRouter $subject, // @phpstan-ignore class.notFound
        callable $proceed,
        string $identifier
    ): bool {
        if ($this->matchBrandParams->execute($identifier)) {
            return false;
        }

        return $proceed($identifier);
    }
}
