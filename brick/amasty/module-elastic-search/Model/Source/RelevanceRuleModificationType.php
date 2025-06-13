<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Source;

class RelevanceRuleModificationType implements \Magento\Framework\Option\ArrayInterface
{
    public const INCREASE = '0';
    public const DECREASE = '1';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::INCREASE, 'label' => __('Increase by')],
            ['value' => self::DECREASE, 'label' => __('Decrease by')]
        ];
    }
}
