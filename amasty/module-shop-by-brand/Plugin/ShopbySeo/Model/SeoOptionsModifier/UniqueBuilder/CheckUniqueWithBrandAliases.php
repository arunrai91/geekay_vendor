<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\ShopbySeo\Model\SeoOptionsModifier\UniqueBuilder;

use Amasty\ShopbyBrand\Helper\Data as BrandHelper;
use Amasty\ShopbySeo\Model\SeoOptionsModifier\UniqueBuilder;

class CheckUniqueWithBrandAliases
{
    /**
     * @var BrandHelper
     */
    private $brandHelper;

    public function __construct(BrandHelper $brandHelper)
    {
        $this->brandHelper = $brandHelper;
    }

    /**
     * @param UniqueBuilder $subject
     * @param bool $result
     * @param string $value
     * @param int|string $optionId
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCheckIfNotUnique(UniqueBuilder $subject, bool $result, string $value, $optionId): bool
    {
        if ($result) {
            return true;
        }

        $aliases = $this->brandHelper->getBrandAliases();
        if (isset($aliases[$optionId])) {
            return false;
        }

        return in_array($value, $aliases, true);
    }
}
