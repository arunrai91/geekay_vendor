<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Block\Adminhtml\Role\Tab;

use Amasty\Rolepermissions\Helper\Data as Helper;
use Amasty\Rolepermissions\Model\Rule;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class Scope extends Generic implements TabInterface
{
    public const MODE_NONE = 0;

    public const MODE_SITE = 1;

    public const MODE_VIEW = 2;

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var Yesno
     */
    private $optionList;

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @var Helper
     */
    private $helper;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Yesno $optionList,
        FieldFactory $fieldFactory,
        Helper $helper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->optionList = $optionList;
        $this->fieldFactory = $fieldFactory;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel(): Phrase
    {
        return __('Advanced: Scope');
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Whether tab is available
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Whether tab is visible
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        /** @var Rule $model */
        $model = $this->_coreRegistry->registry('amrolepermissions_current_rule');

        if (!$model->getId()) {
            $model->setLimitOrders(true)
                ->setLimitInvoices(true)
                ->setLimitShipments(true)
                ->setLimitMemos(true);
        }

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('amrolepermissions_scope_fieldset', ['legend' => __('Choose Access Scope')]);

        $scopeAccessModeValues = [
            self::MODE_NONE => __('Allow All Stores'),
            self::MODE_SITE => __('Specified Websites'),
            self::MODE_VIEW => __('Specified Store Views'),
        ];

        $currentRule = $this->helper->currentRule();

        if ($currentRule && $currentRule->getScopeAccessMode() != self::MODE_NONE) {
            unset($scopeAccessModeValues[self::MODE_NONE]);

            if ($currentRule->getScopeAccessMode() == self::MODE_VIEW) {
                unset($scopeAccessModeValues[self::MODE_SITE]);
            }
        }

        $mode = $fieldset->addField(
            'scope_access_mode',
            'select',
            [
                'label'  => __('Limit Access To'),
                'id'     => 'scope_access_mode',
                'name'   => 'amrolepermissions[scope_access_mode]',
                'values' => $scopeAccessModeValues,
            ]
        );

        $websites = $fieldset->addField(
            'scope_websites',
            'multiselect',
            [
                'name'   => 'amrolepermissions[scope_websites]',
                'label'  => __('Websites'),
                'title'  => __('Websites'),
                'values' => $this->_systemStore->getWebsiteValuesForForm()
            ]
        );
        $renderer = $this->getLayout()->createBlock(
            Element::class
        );
        $websites->setRenderer($renderer);

        $stores = $fieldset->addField(
            'scope_storeviews',
            'multiselect',
            [
                'name'   => 'amrolepermissions[scope_storeviews]',
                'label'  => __('Store Views'),
                'title'  => __('Store Views'),
                'values' => $this->_systemStore->getStoreValuesForForm(false, false),
            ]
        );
        $stores->setRenderer($renderer);

        $limitOrders = $fieldset->addField(
            'limit_orders',
            'select',
            [
                'label'  => __('Limit Access To Orders'),
                'name'   => 'amrolepermissions[limit_orders]',
                'values' => $this->optionList->toOptionArray(),
            ]
        );

        $limitInvoices = $fieldset->addField(
            'limit_invoices',
            'select',
            [
                'label'  => __('Limit Access To Invoices And Transactions'),
                'name'   => 'amrolepermissions[limit_invoices]',
                'values' => $this->optionList->toOptionArray(),
            ]
        );

        $limitShipments = $fieldset->addField(
            'limit_shipments',
            'select',
            [
                'label'  => __('Limit Access To Shipments'),
                'name'   => 'amrolepermissions[limit_shipments]',
                'values' => $this->optionList->toOptionArray(),
            ]
        );

        $limitMemos = $fieldset->addField(
            'limit_memos',
            'select',
            [
                'label'  => __('Limit Access To Credit Memos'),
                'name'   => 'amrolepermissions[limit_memos]',
                'values' => $this->optionList->toOptionArray(),
            ]
        );

        $negativeNone = $this->fieldFactory->create(
            ['fieldData' => ['value' => (string)self::MODE_NONE, 'negative' => 1],  'fieldPrefix' => '']
        );
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                Dependence::class
            )
            ->addFieldMap($mode->getHtmlId(), $mode->getName())
            ->addFieldMap($websites->getHtmlId(), $websites->getName())
            ->addFieldMap($stores->getHtmlId(), $stores->getName())
            ->addFieldMap($limitOrders->getHtmlId(), $limitOrders->getName())
            ->addFieldMap($limitInvoices->getHtmlId(), $limitInvoices->getName())
            ->addFieldMap($limitShipments->getHtmlId(), $limitShipments->getName())
            ->addFieldMap($limitMemos->getHtmlId(), $limitMemos->getName())
            ->addFieldDependence(
                $websites->getName(),
                $mode->getName(),
                self::MODE_SITE
            )
            ->addFieldDependence(
                $stores->getName(),
                $mode->getName(),
                self::MODE_VIEW
            )
            ->addFieldDependence(
                $limitOrders->getName(),
                $mode->getName(),
                $negativeNone
            )
            ->addFieldDependence(
                $limitInvoices->getName(),
                $mode->getName(),
                $negativeNone
            )
            ->addFieldDependence(
                $limitShipments->getName(),
                $mode->getName(),
                $negativeNone
            )
            ->addFieldDependence(
                $limitMemos->getName(),
                $mode->getName(),
                $negativeNone
            )
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
