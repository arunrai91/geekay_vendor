<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class StorageFolder implements OptionSourceInterface
{
    public const MEDIA_FOLDER = 'media';
    public const VAR_FOLDER = 'var';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::MEDIA_FOLDER, 'label' => __('Use \'pub/media\' folder')],
            ['value' => self::VAR_FOLDER, 'label' => __('Use \'var\' folder')]
        ];
    }
}
