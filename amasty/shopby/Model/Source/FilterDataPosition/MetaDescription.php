<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source\FilterDataPosition;

use Amasty\Shopby\Model\Source;

class MetaDescription extends Source\AbstractFilterDataPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return void
     */
    protected function _setLabel()
    {
        $this->_label = __('Meta-Description');
    }
}
