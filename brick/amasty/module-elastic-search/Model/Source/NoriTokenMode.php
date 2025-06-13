<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Source;

class NoriTokenMode implements \Magento\Framework\Option\ArrayInterface
{
    public const NONE = 'none';
    public const DISCARD = 'discard';
    public const MIXED = 'mixed';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NONE, 'label' => __('None')],
            ['value' => self::DISCARD, 'label' => __('Discard')],
            ['value' => self::MIXED, 'label' => __('Mixed')]
        ];
    }
}
