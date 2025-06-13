<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Block\Adminhtml\Slider\Edit;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Block\Adminhtml\Form\Renderer\Fieldset\Element;
use Amasty\ShopbyBrand\Controller\RegistryConstants;
use Magento\Framework\View\Element\BlockInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function _prepareForm()
    {
        $attributeCode = $this->getRequest()->getParam(FilterSettingInterface::ATTRIBUTE_CODE);
        $optionId = $this->getRequest()->getParam('option_id');
        $storeId = $this->getRequest()->getParam('store', 0);
        /** @var \Amasty\ShopbyPage\Api\Data\PageInterface $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::FEATURED);
        $urlParams = [
            'option_id' => (int)$optionId,
            'attribute_code' => $attributeCode,
            'store' => (int)$storeId
        ];
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'class' => 'admin__scope-old',
                    'action' => $this->getUrl('*/*/save', $urlParams),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $form->setUseContainer(true);
        $form->setFieldsetElementRenderer($this->getRenderer());
        $form->setDataObject($model);

        $this->_eventManager->dispatch(
            'amshopby_option_form_build_after',
            [
                'form' => $form,
                'setting' => $model,
                'is_slider' => true,
                'store_id' => $storeId
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    private function getRenderer(): Element
    {
        $name = $this->getNameInLayout() . '_fieldset_base_element';
        $block = $this->getLayout()->getBlock($name);
        if (!$block) {
            $block = $this->getLayout()->createBlock(
                Element::class,
                $name
            );
        }

        return $block;
    }
}
