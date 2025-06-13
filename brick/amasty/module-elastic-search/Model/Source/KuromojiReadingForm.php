<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Source;

class KuromojiReadingForm implements \Magento\Framework\Option\ArrayInterface
{
    public const NONE = 0;
    public const ROMAJI = 1;
    public const KATAKANA = 2;

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NONE, 'label' => __('None')],
            ['value' => self::ROMAJI, 'label' => __('Romaji')],
            ['value' => self::KATAKANA, 'label' => __('Katakana')]
        ];
    }
}
