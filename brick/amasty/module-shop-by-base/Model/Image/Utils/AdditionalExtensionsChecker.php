<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Image\Utils;

class AdditionalExtensionsChecker
{
    public const IMAGICK_EXTENSION = 'imagick';

    public function isImagickEnabled(): bool
    {
        return extension_loaded(self::IMAGICK_EXTENSION);
    }
}
