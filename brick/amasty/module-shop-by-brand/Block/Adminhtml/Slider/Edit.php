<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Block\Adminhtml\Slider;

use Amasty\ShopbyBase\Block\Adminhtml\Widget\Form\Container as WidgetContainer;

/**
 * @api
 */
class Edit extends WidgetContainer
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setObjectId('option_setting_id');
        $this->_controller = 'adminhtml_slider';
        $this->setBlockGroup('Amasty_ShopbyBrand');
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
