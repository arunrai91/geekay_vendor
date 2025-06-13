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
use Amasty\ShopbyBase\Helper\OptionSetting;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockInterface;

/**
 * @api
 */
class Settings extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var OptionSetting
     */
    protected $settingHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        OptionSetting $settingHelper,
        array $data = []
    ) {
        $this->settingHelper = $settingHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $attributeCode = $this->getRequest()->getParam(FilterSettingInterface::ATTRIBUTE_CODE);
        $optionId = (int) $this->getRequest()->getParam('option_id');
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $model = $this->settingHelper->getSettingByOption($optionId, $attributeCode, $storeId);
        $model->setCurrentStoreId($storeId);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
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
