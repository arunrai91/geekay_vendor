<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Product\Attributes;

/**
 * Export-specific storage for feed attributes
 */
class FeedAttributesStorage
{
    /**
     * Attributes prefixes
     */
    public const PREFIX_CATEGORY_ATTRIBUTE = 'category';
    public const PREFIX_CATEGORY_ID_ATTRIBUTE = 'category_id';
    public const PREFIX_CATEGORY_PATH_ATTRIBUTE = 'category_path';
    public const PREFIX_MAPPED_CATEGORY_ATTRIBUTE = 'mapped_category';
    public const PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE = 'mapped_category_path';
    public const PREFIX_CUSTOM_FIELD_ATTRIBUTE = 'custom_field';
    public const PREFIX_PRODUCT_ATTRIBUTE = 'product';
    public const PREFIX_BASIC_ATTRIBUTE = 'basic';
    public const PREFIX_INVENTORY_ATTRIBUTE = 'inventory';
    public const PREFIX_IMAGE_ATTRIBUTE = 'image';
    public const PREFIX_GALLERY_ATTRIBUTE = 'gallery';
    public const PREFIX_PRICE_ATTRIBUTE = 'price';
    public const PREFIX_URL_ATTRIBUTE = 'url';
    public const PREFIX_OTHER_ATTRIBUTES = 'other';
    public const PREFIX_ADVANCED_ATTRIBUTE = 'advanced';

    /**
     * Attributes options
     */
    public const FIRST_SELECTED_CATEGORY = 'first_selected_category';
    public const LAST_SELECTED_CATEGORY = 'last_selected_category';

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $parentAttributes;

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getAttributes(): array
    {
        return $this->attributes ?? [];
    }

    /**
     * @param string $type
     * @return string[]
     */
    public function getAttributesByType(string $type): array
    {
        return $this->attributes[$type] ?? [];
    }

    public function hasAttributes(string $key): bool
    {
        return isset($this->attributes[$key]) && count($this->attributes[$key]) > 0;
    }

    public function setParentAttributes(array $attributes): void
    {
        $this->parentAttributes = $attributes;
    }

    public function getParentAttributes(): array
    {
        return $this->parentAttributes ?? [];
    }

    public function hasParentAttributes(): bool
    {
        $result = false;

        $parentAttributes = $this->getParentAttributes();
        foreach ($parentAttributes as $group) {
            foreach ($group as $attrs) {
                if (isset($attrs)) {
                    $result = true;
                    break;
                }
            }
            if ($result) {
                break;
            }
        }

        if (!$result) {
            $result = isset($this->getAttributesByType(self::PREFIX_URL_ATTRIBUTE)['configurable']);
        }

        return $result;
    }
}
