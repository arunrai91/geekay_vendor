<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Request;

use Amasty\ShopbyBase\Model\OptionSetting as OptionSettingModel;

class BrandRegistry
{
    /**
     * @var OptionSettingModel|null
     */
    private ?OptionSettingModel $brandOption = null;

    public function get(): ?OptionSettingModel
    {
        return $this->brandOption;
    }

    public function set(?OptionSettingModel $brandOption): void
    {
        $this->brandOption = $brandOption;
    }
}
