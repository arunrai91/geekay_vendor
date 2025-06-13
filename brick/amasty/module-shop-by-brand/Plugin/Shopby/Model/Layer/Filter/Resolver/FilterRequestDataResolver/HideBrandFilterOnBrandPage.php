<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver;

use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver as FilterDataResolver;
use Amasty\ShopbyBrand\Model\BrandResolver;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class HideBrandFilterOnBrandPage
{
    /**
     * @var BrandResolver
     */
    private BrandResolver $brandResolver;

    public function __construct(BrandResolver $brandResolver)
    {
        $this->brandResolver = $brandResolver;
    }

    public function afterIsVisibleWhenSelected(
        FilterDataResolver $subject, // @phpstan-ignore class.notFound
        bool $result,
        FilterInterface $filter
    ): bool {
        return ($result && $this->brandResolver->isBrandFilter($filter)) ? false : $result;
    }
}
