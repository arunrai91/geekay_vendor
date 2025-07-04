<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Source;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttributeResource;

class DisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    public const MODE_DEFAULT = 0;
    public const MODE_DROPDOWN = 1;
    public const MODE_SLIDER = 2;
    public const MODE_FROM_TO_ONLY = 3;
    public const MODE_IMAGES = 4;
    public const MODE_IMAGES_LABELS = 5;
    public const MODE_TEXT_SWATCH = 6;

    public const ATTRUBUTE_DEFAULT = 'default';
    public const ATTRUBUTE_PRICE = 'price';

    /**
     * @var string
     */
    private string $attributeType = self::ATTRUBUTE_DEFAULT;

    /**
     * @var EavAttributeResource|null
     */
    private ?EavAttributeResource $attribute = null;

    /**
     * @param string $attributeType
     * @return $this
     */
    public function setAttributeType($attributeType)
    {
        $this->attributeType = $attributeType;
        return $this;
    }

    /**
     * @param EavAttributeResource $attribute
     * @return $this
     */
    public function setAttribute(EavAttributeResource $attribute)
    {
        $this->setAttributeType($attribute->getBackendType());
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @return bool
     */
    public function showSwatchOptions()
    {
        $show = true;

        if ($this->attribute
            && $this->attribute->getId()
            && $this->attribute->getFrontendInput() != 'select'
        ) {
            $show = false;
        }

        return $show;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getOptions() as $optionValue => $optionLabel) {
            $options[] = ['value' => $optionValue, 'label' => $optionLabel];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getOptions();
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        $options = [
            "" => "",
            self::MODE_DEFAULT => __('Labels'),
            self::MODE_DROPDOWN => __('Dropdown')
        ];

        if ($this->showSwatchOptions()) {
            $options[self::MODE_IMAGES] = __('Images');
            $options[self::MODE_IMAGES_LABELS] = __('Images & Labels');
            $options[self::MODE_TEXT_SWATCH] = __('Text Swatches');
        }

        $options[self::MODE_SLIDER] = __('Slider');
        $options[self::MODE_FROM_TO_ONLY] = __('From-To Only');

        return $options;
    }

    /**
     * @return array
     */
    public function getInputTypeMap()
    {
        $array = [
            self::ATTRUBUTE_DEFAULT => [
                self::MODE_DEFAULT => 'multiselect',
                self::MODE_DROPDOWN => 'select'
            ],
            self::ATTRUBUTE_PRICE => [
                self::MODE_DEFAULT => self::ATTRUBUTE_PRICE,
                self::MODE_DROPDOWN => self::ATTRUBUTE_PRICE,
                self::MODE_SLIDER => self::ATTRUBUTE_PRICE,
                self::MODE_FROM_TO_ONLY => self::ATTRUBUTE_PRICE,
            ]
        ];
        if ($this->showSwatchOptions()) {
            $array[self::ATTRUBUTE_DEFAULT][self::MODE_IMAGES] =
                \Magento\Swatches\Model\Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT;
            $array[self::ATTRUBUTE_DEFAULT][self::MODE_IMAGES_LABELS] =
                \Magento\Swatches\Model\Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT;
            $array[self::ATTRUBUTE_DEFAULT][self::MODE_TEXT_SWATCH] =
                \Magento\Swatches\Model\Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT;
        }

        return $array;
    }

    /**
     * @return array
     */
    public function getAllOptionsDependencies()
    {
        $options = [
            self::ATTRUBUTE_DEFAULT => [
                self::MODE_DEFAULT => __('Labels'),
                self::MODE_DROPDOWN => __('Dropdown')
            ],
            self::ATTRUBUTE_PRICE => [
                self::MODE_DEFAULT => __('Ranges'),
                self::MODE_DROPDOWN => __('Dropdown'),
                self::MODE_SLIDER => __('Slider'),
                self::MODE_FROM_TO_ONLY => __('From-To Only'),
            ]
        ];
        if ($this->showSwatchOptions()) {
            $options[self::ATTRUBUTE_DEFAULT][self::MODE_IMAGES] = __('Images');
            $options[self::ATTRUBUTE_DEFAULT][self::MODE_IMAGES_LABELS] = __('Images & Labels');
            $options[self::ATTRUBUTE_DEFAULT][self::MODE_TEXT_SWATCH] = __('Text Swatches');
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getShowProductQuantitiesConfig()
    {
        return [
            self::MODE_DEFAULT,
            self::MODE_DROPDOWN,
            self::MODE_IMAGES_LABELS
        ];
    }

    /**
     * @return array
     */
    public function getNumberUnfoldedOptionsConfig()
    {
        return [
            self::MODE_DEFAULT,
            self::MODE_IMAGES_LABELS,
            self::MODE_IMAGES,
            self::MODE_TEXT_SWATCH
        ];
    }

    /**
     * @return array
     */
    public function getIsMultiselectConfig()
    {
        return [
            self::MODE_DEFAULT,
            self::MODE_DROPDOWN,
            self::MODE_IMAGES_LABELS,
            self::MODE_IMAGES,
            self::MODE_TEXT_SWATCH
        ];
    }

    /**
     * @return array
     */
    public function getNotices()
    {
        return [
            self::MODE_IMAGES => __('Please upload images at the Properties tab.'),
            self::MODE_IMAGES_LABELS => __('Please upload images at the Properties tab.'),
            self::MODE_TEXT_SWATCH => __('Please add text values at the Properties tab.')
        ];
    }

    /**
     * @return array
     */
    public function getEnabledTypes()
    {
        return [
            self::MODE_DROPDOWN => 'select',
            self::MODE_IMAGES => \Magento\Swatches\Model\Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT,
            self::MODE_TEXT_SWATCH => \Magento\Swatches\Model\Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT
        ];
    }

    /**
     * @return array
     */
    public function getChangeLabels()
    {
        return [
            self::ATTRUBUTE_DEFAULT => [self::MODE_DEFAULT => __('Labels')],
            self::ATTRUBUTE_PRICE => [self::MODE_DEFAULT => __('Ranges')]
        ];
    }
}
