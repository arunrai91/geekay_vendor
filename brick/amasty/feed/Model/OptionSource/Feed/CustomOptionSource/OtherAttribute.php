<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\OptionSource\Feed\CustomOptionSource;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;

class OtherAttribute implements CustomOptionSourceInterface
{
    /**
     * @var Utils\ArrayCustomizer
     */
    private $arrayCustomizer;

    public function __construct(
        Utils\ArrayCustomizer $arrayCustomizer
    ) {
        $this->arrayCustomizer = $arrayCustomizer;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        $attributes = [
            'tax_percents' => __('Tax Percents'),
            'sale_price_effective_date' => __('Sale Price Effective Date'),
        ];

        return $this->arrayCustomizer->customizeArray($attributes, FeedAttributesStorage::PREFIX_OTHER_ATTRIBUTES);
    }
}
