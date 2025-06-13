<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Adminhtml\System\Config\Form\Field;

class Bestsellers extends AbstractField
{
    public const MODULE_NAME = 'Amasty_Sorting';
    public const CONFIG_MODULE_NAME = 'bestsellers';

    protected function getNote()
    {
        return __(
            'Increase the convenience of searching for products by customers with the '
            . 'help of various sorting options provided by Improved Sorting plugin.'
        );
    }

    protected function getStatus()
    {
        return __('Amasty Sorting Not Installed');
    }
}
