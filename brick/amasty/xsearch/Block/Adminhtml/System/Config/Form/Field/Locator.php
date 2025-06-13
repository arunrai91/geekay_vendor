<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Adminhtml\System\Config\Form\Field;

class Locator extends AbstractField
{
    public const MODULE_NAME = 'Amasty_StoreLocatorAdvancedSearch';
    public const CONFIG_MODULE_NAME = 'locator';

    /**
     * @return string
     */
    protected function getNote()
    {
        return __('Allows to search by store locations created with Amasty Store Locator extension
            (Please note: for the functionality correct operation you will additionally need to install
            the \'amasty/module-store-locator-advanced-search\' package,
            you can find it in amasty/xsearch module composer suggest).')->render();
    }
}
