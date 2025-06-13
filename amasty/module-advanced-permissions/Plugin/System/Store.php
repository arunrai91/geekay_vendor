<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\System;

class Store
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    public function __construct(\Amasty\Rolepermissions\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    public function aroundGetStoreValuesForForm(
        \Magento\Store\Model\System\Store $subject,
        \Closure $proceed,
        $empty = false,
        $all = false
    ) {
        if ($rule = $this->helper->currentRule()) {
            $allowedStores = $rule->getScopeStoreviews();

            if ($allowedStores) {
                $all = false;
            }
        }

        return $proceed($empty, $all);
    }

    public function aroundGetWebsiteValuesForForm(
        \Magento\Store\Model\System\Store $subject,
        \Closure $proceed,
        $empty = false,
        $all = false
    ) {
        return $this->aroundGetStoreValuesForForm($subject, $proceed, $empty, $all);
    }
}
