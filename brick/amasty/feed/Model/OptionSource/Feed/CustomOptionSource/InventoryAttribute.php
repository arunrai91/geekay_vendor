<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\OptionSource\Feed\CustomOptionSource;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;

class InventoryAttribute implements CustomOptionSourceInterface
{
    public const QTY = 'qty';
    public const IS_IN_STOCK = 'is_in_stock';
    public const SALABLE_QTY = 'salable_qty';

    /**
     * @var Utils\ArrayCustomizer
     */
    private $arrayCustomizer;

    public function __construct(
        Utils\ArrayCustomizer $arrayCustomizer
    ) {
        $this->arrayCustomizer = $arrayCustomizer;
    }

    public function getOptions(): array
    {
        $attributes = [
            self::QTY => __('Qty'),
            self::IS_IN_STOCK => __('Is In Stock'),
            self::SALABLE_QTY => __('Salable Qty')
        ];

        return $this->arrayCustomizer->customizeArray($attributes, FeedAttributesStorage::PREFIX_INVENTORY_ATTRIBUTE);
    }
}
