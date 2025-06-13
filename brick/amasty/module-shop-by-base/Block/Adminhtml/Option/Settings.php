<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\Option;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Block\Adminhtml\Form\Renderer\Fieldset\Element;
use Amasty\ShopbyBase\Block\Adminhtml\Widget\Form as WidgetForm;
use Amasty\ShopbyBase\Block\Adminhtml\Widget\Form\Element\ElementCreator;
use Amasty\ShopbyBase\Helper\OptionSetting;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;

/**
 * @api
 */
class Settings extends WidgetForm
{
    /**
     * @var OptionSetting
     */
    private OptionSetting $settingHelper;

    public function __construct(
        OptionSetting $settingHelper,
        FormFactory $formFactory,
        ElementCreator $creator,
        Context $context,
        array $data = []
    ) {
        $this->settingHelper = $settingHelper;
        parent::__construct($formFactory, $creator, $context, $data);
    }

    public function prepareForm(): Settings
    {
        $attributeCode = $this->getRequest()->getParam(FilterSettingInterface::ATTRIBUTE_CODE);
        $optionId = (int) $this->getRequest()->getParam('option_id');
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $model = $this->settingHelper->getSettingByOption($optionId, $attributeCode, $storeId);
        $model->setCurrentStoreId($storeId);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->getDataFormFactory()->create(
            [
                'data' => [
                    'id' => 'edit_options_form',
                    'class' => 'admin__scope-old',
                    'action' => $this->getUrl('*/*/save', [
                        'option_id' => (int)$optionId,
                        'attribute_code' => $attributeCode,
                        'store' => (int)$storeId
                    ]),
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
                'store_id' => $storeId
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::prepareForm();
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
