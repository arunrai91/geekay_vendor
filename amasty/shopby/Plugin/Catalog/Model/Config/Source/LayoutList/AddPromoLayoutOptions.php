<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Catalog\Model\Config\Source\LayoutList;

use Magento\Catalog\Model\Config\Source\LayoutList;
use Magento\Framework\Module\Manager as ModuleManager;

class AddPromoLayoutOptions
{
    public const PROMO_LAYOUT_OPTIONS = [
        [
            'value' => 'amshopby-2columns-left-collapsed',
            'label' => 'Amasty 2 columns with left bar (for categories) Available with product subscription',
            'disabled' => true
        ],
        [
            'value' => 'amshopby-2columns-right-collapsed',
            'label' => 'Amasty 2 columns with right bar (for categories) Available with product subscription',
            'disabled' => true
        ]
    ];

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        ModuleManager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param LayoutList $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToOptionArray(
        LayoutList $subject,
        array $result
    ): array {
        foreach (self::PROMO_LAYOUT_OPTIONS as $promoLayout) {
            if (!$this->isAlreadyHaveValue($result, $promoLayout['value'])) {
                $result[] = $promoLayout;
            }
        }

        return $result;
    }

    private function isAlreadyHaveValue(array $options, string $value): bool
    {
        return !empty(array_filter($options, function ($item) use ($value) {
            return $item['value'] === $value;
        }));
    }
}
