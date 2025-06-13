<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\FilterSetting;

use Amasty\ShopbyBase\Model\Source\DisplayMode;

class IsMultiselect
{
    public function execute(?string $attributeCode, ?bool $isMultiselect, ?int $displayMode): bool
    {
        if (!$attributeCode) {
            return false;
        }

        return $isMultiselect
            && $this->isDisplayTypeAllowsMultiselect($displayMode);
    }

    private function isDisplayTypeAllowsMultiselect(int $displayMode): bool
    {
        return in_array($displayMode, $this->getMultiSelectModes());
    }

    private function getMultiSelectModes(): array
    {
        return [
            DisplayMode::MODE_DEFAULT,
            DisplayMode::MODE_DROPDOWN,
            DisplayMode::MODE_IMAGES,
            DisplayMode::MODE_IMAGES_LABELS,
            DisplayMode::MODE_TEXT_SWATCH
        ];
    }
}
