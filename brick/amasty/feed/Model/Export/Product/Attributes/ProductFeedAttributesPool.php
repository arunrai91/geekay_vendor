<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Product\Attributes;

class ProductFeedAttributesPool
{
    /**
     * @var string[]
     */
    protected $basicTypes = [
        FeedAttributesStorage::PREFIX_BASIC_ATTRIBUTE,
        FeedAttributesStorage::PREFIX_PRODUCT_ATTRIBUTE,
        FeedAttributesStorage::PREFIX_INVENTORY_ATTRIBUTE
    ];

    /**
     * @var string[]
     */
    private $customTypes;

    public function __construct(
        array $customTypes = []
    ) {
        $this->customTypes = array_values($customTypes);
    }

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        return array_merge($this->getBasicTypes(), $this->getCustomTypes());
    }

    /**
     * @return string[]
     */
    public function getBasicTypes(): array
    {
        return $this->basicTypes ?? [];
    }

    /**
     * @return string[]
     */
    public function getCustomTypes(): array
    {
        return $this->customTypes ?? [];
    }
}
