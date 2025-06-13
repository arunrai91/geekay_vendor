<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Compress implements OptionSourceInterface
{
    public const COMPRESS_NONE = '';
    public const COMPRESS_ZIP = 'zip';
    public const COMPRESS_GZ = 'gz';
    public const COMPRESS_BZ = 'bz2';

    public function toOptionArray(): array
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    public function toArray(): array
    {
        return [
            self::COMPRESS_NONE => __('None'),
            self::COMPRESS_ZIP => __('Zip'),
            self::COMPRESS_GZ => __('Gz'),
            self::COMPRESS_BZ => __('Bz')
        ];
    }
}
