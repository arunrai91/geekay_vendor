<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\SalesRule\Block\Adminhtml\Promo\Quote\Edit;

class GenericButton
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data $helper
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry $registry
     */
    private $registry;

    /**
     * GenericButton constructor.
     * @param \Amasty\Rolepermissions\Helper\Data $helper
     */
    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    public function afterCanRender($subject, $result)
    {
        /** @var \Amasty\Rolepermissions\Model\Rule $rule */
        $rule = $this->helper->currentRule();
        $cartPriceRule = $this->registry->registry('cart_price_rule');
        $removeButtons = [
            'save',
            'save_and_continue_edit',
            'delete'
        ];

        if (!$rule->isAttributesInRole($cartPriceRule, $rule::CART)) {
            if (in_array($result, $removeButtons)) {
                $result = false;
            }
        }

        return $result;
    }
}
