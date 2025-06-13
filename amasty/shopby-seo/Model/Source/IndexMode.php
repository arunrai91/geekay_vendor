<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\Source;

class IndexMode implements \Magento\Framework\Option\ArrayInterface
{
    public const MODE_NEVER = 0;
    public const MODE_SINGLE_ONLY = 1;
    public const MODE_ALWAYS  = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->_getOptions() as $optionValue => $optionLabel) {
            $options[] = ['value'=>$optionValue, 'label'=>$optionLabel];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_getOptions();
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = [
            self::MODE_NEVER => __('Never'),
            self::MODE_SINGLE_ONLY => __('Single Selection Only'),
            self::MODE_ALWAYS => __('Always'),
        ];

        return $options;
    }
}
