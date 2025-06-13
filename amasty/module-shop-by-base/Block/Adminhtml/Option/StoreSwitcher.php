<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\Option;

class StoreSwitcher extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $optionId = $this->getRequest()->getParam('option_id');
        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'preview_form',
                    'action' => $this->getUrl('*/*/settings', [
                        'option_id' => (int)$optionId,
                        'attribute_code' => $attributeCode
                    ]),
                ],
            ]
        );
        $form->setUseContainer(true);
        $form->addField('preview_selected_store', 'hidden', ['name' => 'store', 'id'=>'preview_selected_store']);

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
