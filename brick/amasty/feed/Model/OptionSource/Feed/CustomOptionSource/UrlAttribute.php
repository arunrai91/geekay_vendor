<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\OptionSource\Feed\CustomOptionSource;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;

class UrlAttribute implements CustomOptionSourceInterface
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

    public function getOptions(): array
    {
        $attributes = [
            'short' => __('Short'),
            'with_category' => __('With Category'),
            'configurable' => __('With Predefined Product Options')
        ];

        return $this->arrayCustomizer->customizeArray($attributes, FeedAttributesStorage::PREFIX_URL_ATTRIBUTE);
    }
}
