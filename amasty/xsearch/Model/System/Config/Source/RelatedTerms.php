<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\System\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class RelatedTerms implements ArrayInterface
{
    public const DISABLED = 0;
    public const SHOW_ALWAYS = 1;
    public const SHOW_ONLY_WITHOUT_RESULTS = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::DISABLED => __('No, Disabled'),
            self::SHOW_ALWAYS => __('Yes, Show Always'),
            self::SHOW_ONLY_WITHOUT_RESULTS => __('Yes, Show Only when search returns 0 results'),
        ];

        return $options;
    }
}
