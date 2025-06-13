<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Source;

class CombiningType implements \Magento\Framework\Option\ArrayInterface
{
    public const ANY = '0';
    public const ALL = '1';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [['value' => self::ANY, 'label' => __('OR')], ['value' => self::ALL, 'label' => __('AND')]];
    }
}
