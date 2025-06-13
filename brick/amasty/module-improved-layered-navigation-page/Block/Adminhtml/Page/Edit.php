<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Block\Adminhtml\Page;

use Amasty\ShopbyBase\Block\Adminhtml\Widget\Form\Container;

/**
 * @api
 */
class Edit extends Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setObjectId('id');
        $this->_controller = 'adminhtml_page';
        $this->setBlockGroup('Amasty_ShopbyPage');

        parent::_construct();

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );
    }
}
