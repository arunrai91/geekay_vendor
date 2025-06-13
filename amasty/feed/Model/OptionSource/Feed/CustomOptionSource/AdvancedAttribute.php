<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\OptionSource\Feed\CustomOptionSource;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;

class AdvancedAttribute implements CustomOptionSourceInterface
{
    public const ATTRIBUTES = [
        'category_ids' => 'Category Ids',
    ];

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
        return $this->arrayCustomizer->customizeArray(
            self::ATTRIBUTES,
            FeedAttributesStorage::PREFIX_ADVANCED_ATTRIBUTE
        );
    }
}
