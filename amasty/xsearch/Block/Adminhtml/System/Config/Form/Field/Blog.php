<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Adminhtml\System\Config\Form\Field;

class Blog extends AbstractField
{
    public const MODULE_NAME = 'Amasty_Blog';
    public const CONFIG_MODULE_NAME = 'blog';

    /**
     * @return \Magento\Framework\Phrase|string
     */
    protected function getNote()
    {
        return __('Allows to search by blog pages created with Amasty Blog extension.');
    }
}
