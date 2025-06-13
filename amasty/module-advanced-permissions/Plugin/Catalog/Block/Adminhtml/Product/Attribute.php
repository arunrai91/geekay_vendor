<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Catalog\Block\Adminhtml\Product;

use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Scope;

class Attribute
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Block\Adminhtml\Product\Attribute $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterCanRender($subject, $result)
    {
        $rule = $this->helper->currentRule();

        if ($rule->getScopeAccessMode() != Scope::MODE_NONE) {
            return false;
        }

        return $result;
    }
}
