<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\Option;

use Amasty\ShopbyBase\Block\Adminhtml\Widget\Form as WidgetForm;

class StoreSwitcher extends WidgetForm
{
    public function prepareForm(): StoreSwitcher
    {
        $optionId = $this->getRequest()->getParam('option_id');
        $attributeCode = $this->getRequest()->getParam('attribute_code');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->getDataFormFactory()->create(
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

        return parent::prepareForm();
    }
}
