<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\Method;

use Amasty\Sorting\Model\MethodProvider;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ObjectManager;

/**
 * Check if passed code is amasty method or attribute used for sorting.
 */
class IsAvailableForSorting
{
    public const DEFAULT_POSITION_SORTING_CODE = 'position';

    /**
     * @var MethodProvider
     */
    private $methodProvider;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    public function __construct(
        ?CatalogConfig $catalogConfig, // @deprecated
        MethodProvider $methodProvider = null,
        EavConfig $eavConfig = null
    ) {
        $this->methodProvider = $methodProvider ?? ObjectManager::getInstance()->get(MethodProvider::class);
        $this->eavConfig = $eavConfig ?? ObjectManager::getInstance()->get(EavConfig::class);
    }

    public function execute(string $sortingCode): bool
    {
        if ($this->methodProvider->getMethodByCode($sortingCode)
            || $sortingCode === self::DEFAULT_POSITION_SORTING_CODE
        ) {
            return true;
        }

        $attribute = $this->eavConfig->getAttribute(ProductModel::ENTITY, $sortingCode);
        return $attribute && $attribute->getUsedForSortBy();
    }
}
