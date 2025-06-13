<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Catalog\Model\ResourceModel\Product\Collection;

use Amasty\ShopbyBrand\Model\BrandResolver;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class FixUrlRewrites
{
    /**
     * @var BrandResolver
     */
    private $brandResolver;

    public function __construct(BrandResolver $brandResolver)
    {
        $this->brandResolver = $brandResolver;
    }

    /**
     * @param Collection $subject
     * @param int|string $categoryId
     * @return array
     * @see Collection::addUrlRewrite()
     */
    public function beforeAddUrlRewrite(Collection $subject, $categoryId = ''): array
    {
        if ($this->brandResolver->getCurrentBrand()) {
            $categoryId = 0;
        }

        return [$categoryId];
    }
}
