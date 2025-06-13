<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Catalog;

use Amasty\Shopby\Helper\FilterSetting as FilterSettingHelper;
use Amasty\Shopby\Model\Source\DisplayMode;
use Closure;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Model\Swatch;

class Swatches
{
    /**
     * @var FilterSettingHelper
     */
    private $filterSettingHelper;

    public function __construct(
        FilterSettingHelper $filterSettingHelper
    ) {
        $this->filterSettingHelper = $filterSettingHelper;
    }

    public function aroundIsSwatchAttribute(
        Data $subject,
        Closure $closure,
        Attribute $attribute
    ): bool {
        $isSwatchAttribute = $closure($attribute);
        $filterSetting = $this->filterSettingHelper->getSettingByAttribute($attribute);

        if (!$isSwatchAttribute) {
            $isSwatchAttribute = $filterSetting && in_array(
                $filterSetting->getDisplayMode(),
                [DisplayMode::MODE_IMAGES_LABELS, DisplayMode::MODE_IMAGES]
            );
        }

        if ($isSwatchAttribute
            && $filterSetting
            && $filterSetting->getDisplayMode() === DisplayMode::MODE_DEFAULT
            && $this->isTextSwatch($attribute)
        ) {
                $isSwatchAttribute = false;
        }

        return $isSwatchAttribute;
    }

    private function isTextSwatch(Attribute $attribute): bool
    {
        return $attribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) === Swatch::SWATCH_INPUT_TYPE_TEXT;
    }
}
