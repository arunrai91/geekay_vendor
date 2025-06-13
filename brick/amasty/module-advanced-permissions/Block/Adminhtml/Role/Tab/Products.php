<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Block\Adminhtml\Role\Tab;

class Products extends \Magento\Backend\Block\Widget\Form\Generic
{
    public const MODE_ANY = 0;
    public const MODE_SELECTED = 1;
    public const MODE_MY = 2;
    public const MODE_SCOPE = 3;

    protected function _prepareForm()
    {
        /** @var \Amasty\Rolepermissions\Model\Rule $model */
        $model = $this->_coreRegistry->registry('amrolepermissions_current_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('amrolepermissions_products_fieldset', ['legend' => __('Product Access')]);

        $grid = $this->getChildBlock('grid');

        $mode = $fieldset->addField('product_access_mode', 'select', [
            'label' => __('Allow Access To'),
            'id'    => 'amrolepermissions[product_access_mode]',
            'name'  => 'amrolepermissions[product_access_mode]',
            'values'=> [
                self::MODE_ANY => __('All Products'),
                self::MODE_SELECTED => __('Selected Products'),
                self::MODE_MY => __('Own Created Products'),
                self::MODE_SCOPE => __('Users in same role')
            ]
        ]);

        $fieldset->addField('products_list', 'hidden', [
            'after_element_html' => "<div>{$grid->toHtml()}</div>",
        ]);

        $form->addValues($model->getData());
        $this->setForm($form);
        $formAfter = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
            ->addFieldMap($mode->getHtmlId(), $mode->getName())
            ->addFieldMap('amrolepremissions_allowed_product_grid', 'amrolepremissions_allowed_product_grid')
            ->addFieldDependence(
                'amrolepremissions_allowed_product_grid',
                $mode->getName(),
                self::MODE_SELECTED
            );

        $this->setChild(
            'form_after',
            $formAfter
        );

        return parent::_prepareForm();
    }
}
