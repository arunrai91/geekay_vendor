<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\System\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class DynamicSearchWidth implements ArrayInterface
{
    public const DEFAULT_WIDTH = 0;
    public const DYNAMIC_WIDTH = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::DYNAMIC_WIDTH => __('Dynamic (based on popup width)'),
            self::DEFAULT_WIDTH => __('Default')
        ];

        return $options;
    }
}
