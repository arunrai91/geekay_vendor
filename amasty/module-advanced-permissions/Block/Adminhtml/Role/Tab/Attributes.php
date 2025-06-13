<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Block\Adminhtml\Role\Tab;

use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Attributes\Grid;
use Amasty\Rolepermissions\Model\Rule;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Registry;

class Attributes extends Generic
{
    public const MODE_ANY = 0;
    public const MODE_SELECTED = 1;

    private const AMASTY_PGRID_MODULE_NAME = 'Amasty_Pgrid';

    /**
     * @var Yesno
     */
    private $optionList;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $optionList,
        ModuleManager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->optionList = $optionList;
        $this->moduleManager = $moduleManager;
    }

    protected function _prepareForm()
    {
        /** @var Rule $model */
        $model = $this->_coreRegistry->registry('amrolepermissions_current_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('amrolepermissions_attributes_fieldset', ['legend' => __('Attributes Access')]);

        $grid = $this->getChildBlock('grid');

        $mode = $fieldset->addField('attribute_access_mode', 'select', [
            'label' => __('Allow Access To'),
            'id'    => 'amrolepermissions[attribute_access_mode]',
            'name'  => 'amrolepermissions[attribute_access_mode]',
            'values'=> [
                self::MODE_ANY => __('All Attributes'),
                self::MODE_SELECTED => __('Selected Attributes'),
            ]
        ]);

        $formAfter = $this
            ->getLayout()->createBlock(Dependence::class)
            ->addFieldMap($mode->getHtmlId(), $mode->getName());

        $limitProductSourcesManagement = $fieldset->addField(
            'limit_product_sources_management',
            'select',
            [
                'label'  => __('Restrict Access To Product Sources Data'),
                'id'    => 'amrolepermissions[limit_product_sources_management]',
                'name'   => 'amrolepermissions[limit_product_sources_management]',
                'values' => $this->optionList->toOptionArray(),
            ]
        );
        $formAfter->addFieldMap(
            $limitProductSourcesManagement->getHtmlId(),
            $limitProductSourcesManagement->getName()
        )->addFieldDependence(
            $limitProductSourcesManagement->getName(),
            $mode->getName(),
            self::MODE_SELECTED
        );

        if ($this->moduleManager->isEnabled(self::AMASTY_PGRID_MODULE_NAME)) {
            $limitPgridExtra = $fieldset->addField(
                'limit_pgrid_extra',
                'select',
                [
                    'label'  => __('Limit Access To Amasty Product Grid Extra Columns'),
                    'id'    => 'amrolepermissions[limit_pgrid_extra]',
                    'name'   => 'amrolepermissions[limit_pgrid_extra]',
                    'values' => $this->optionList->toOptionArray(),
                ]
            );
            $formAfter->addFieldMap($limitPgridExtra->getHtmlId(), $limitPgridExtra->getName())
                ->addFieldDependence(
                    $limitPgridExtra->getName(),
                    $mode->getName(),
                    self::MODE_SELECTED
                );
        }

        $fieldset->addField('attributes_list', 'hidden', [
            'after_element_html' => "<div>{$grid->toHtml()}</div>",
        ]);

        $formAfter->addFieldMap(Grid::GRID_ID, Grid::GRID_ID)
            ->addFieldDependence(
                Grid::GRID_ID,
                $mode->getName(),
                self::MODE_SELECTED
            );

        $this->setChild('form_after', $formAfter);
        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
