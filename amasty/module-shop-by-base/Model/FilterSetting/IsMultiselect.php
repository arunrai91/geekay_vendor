<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\FilterSetting;

use Amasty\ShopbyBase\Helper\Data;
use Amasty\ShopbyBase\Model\Integration\ShopbyBrand\GetConfigProvider;
use Amasty\ShopbyBase\Model\Source\DisplayMode;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Framework\Registry;

class IsMultiselect
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ConfigProvider|null
     */
    private $brandConfigProvider;

    public function __construct(
        Registry $registry,
        GetConfigProvider $getConfigProvider
    ) {
        $this->registry = $registry;
        $this->brandConfigProvider = $getConfigProvider->execute();
    }

    public function execute(?string $attributeCode, ?bool $isMultiselect, ?int $displayMode): bool
    {
        if (!$this->brandConfigProvider || !$attributeCode) {
            return false;
        }

        $allProducts = $this->registry->registry(Data::SHOPBY_CATEGORY_INDEX);
        $isBrandOnAllProducts = $attributeCode === $this->brandConfigProvider->getBrandAttributeCode()
            && isset($allProducts);

        return $isMultiselect
            && $this->isDisplayTypeAllowsMultiselect($displayMode)
            && !$isBrandOnAllProducts;
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
