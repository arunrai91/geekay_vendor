<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2022-08-16T14:28:38+00:00
 * File:          Block/Adminhtml/Profile/Edit/Tab/Filters.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Profile\Edit\Tab;

use Xtento\OrderExport\Model\Export;

class Filters extends \Xtento\OrderExport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSource;

    /**
     * @var \Xtento\OrderExport\Model\System\Config\Source\Export\Status
     */
    protected $exportStatus;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $productType;

    /**
     * @var \Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses
     */
    protected $allStatuses;

    /**
     * @var \Xtento\OrderExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * Filters constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Magento\Store\Model\System\Store $storeSource
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Xtento\OrderExport\Model\System\Config\Source\Export\Status $exportStatus
     * @param \Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses $allStatuses
     * @param \Xtento\OrderExport\Helper\Entity $entityHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Store\Model\System\Store $storeSource,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Catalog\Model\Product\Type $productType,
        \Xtento\OrderExport\Model\System\Config\Source\Export\Status $exportStatus,
        \Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses $allStatuses,
        \Xtento\OrderExport\Helper\Entity $entityHelper,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->storeSource = $storeSource;
        $this->exportStatus = $exportStatus;
        $this->conditions = $conditions;
        $this->rendererFieldset = $rendererFieldset;
        $this->productType = $productType;
        $this->allStatuses = $allStatuses;
        $this->entityHelper = $entityHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function getFormMessages()
    {
        $formMessages = [];
        $formMessages[] = [
            'type' => 'notice',
            'message' => __(
                'The settings specified below will be applied to all manual and automatic exports. For manual exports, this can be changed in the "Manual Export" screen before exporting. If an %1 does not match the filters, it simply won\'t be exported.',
                $this->_coreRegistry->registry('orderexport_profile')->getEntity()
            )
        ];
        return $formMessages;
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('orderexport_profile');
        if (!$model->getId()) {
            return $this;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $entity = $model->getEntity();
        $entityName = $this->entityHelper->getEntityName($entity);
        $fieldset = $form->addFieldset(
            'object_filters',
            ['legend' => __('%1 Filters', ucwords($model->getEntity())), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'export_filter_new_only',
            'select',
            [
                'label' => __('Export only new %1s', $entityName),
                'name' => 'export_filter_new_only',
                'values' => $this->yesNo->toOptionArray(),
                'note' => __(
                    'Regardless whether you\'re using manual, cronjob or the event-based export, if set to yes, this setting will make sure every %1 gets exported only ONCE by this profile. This means, even if another export event gets called, if the %2 has been already exported by this profile, it won\'t be exported again. You can "reset" exported objects in the "Profile Export History" tab.<br/>Example usage: Set up a cronjob export which exports all "Processing" orders and set this to "Yes" - every "Processing" order will be exported only ONCE.',
                    $entityName,
                    $entityName
                )
            ]
        );

        $fieldset->addField(
            'store_ids',
            'multiselect',
            [
                'label' => __('Store Views'),
                'name' => 'store_ids[]',
                'values' => array_merge_recursive(
                    [['value' => '', 'label' => __('--- All Store Views ---')]],
                    $this->storeSource->getStoreValuesForForm()
                ),
                'note' => __(
                    'Leave empty or select all to export any store. Hold CTRL on your keyboard to pick specific stores.'
                ),
            ]
        );

        if ($entity !== Export::ENTITY_EERMA) {
            $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            $fieldset->addField(
                'export_filter_datefrom',
                'date',
                [
                    'label' => __('Date From'),
                    'name' => 'export_filter_datefrom',
                    'date_format' => $dateFormat,
                    'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                    'note' => __('Export only %1s created after date X (including day X).', $entity),
                    'class' => 'validate-date'
                ]
            );

            $fieldset->addField(
                'export_filter_dateto',
                'date',
                [
                    'label' => __('Date To'),
                    'name' => 'export_filter_dateto',
                    'date_format' => $dateFormat,
                    'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                    'note' => __('Export only %1s created before date X (including day X).', $entity),
                    'class' => 'validate-date'
                ]
            );

            $fieldset->addField(
                'export_filter_last_x_days',
                'text',
                [
                    'label' => __('Created during the last X days'),
                    'name' => 'export_filter_last_x_days',
                    'maxlength' => 5,
                    'style' => 'width: 70px !important;',
                    'note' => __(
                        'Export only %1s created during the last X days (including day X). Only enter numbers here, nothing else. Leave empty if no "created during the last X days" filter should be applied. Minimum value: 0',
                        $entity
                    )
                ]
            )->setType('number');

            $fieldset->addField(
                'export_filter_older_x_minutes',
                'text',
                [
                    'label' => __('Older than X minutes'),
                    'name' => 'export_filter_older_x_minutes',
                    'maxlength' => 10,
                    'style' => 'width: 75px !important;',
                    'note' => __(
                        'Export only %1s which have been created at least X minutes ago. Only enter numbers here, nothing else. Leave empty if no filter should be applied. Minimum value: 1',
                        $entity
                    )
                ]
            )->setType('number');
        }

        if ($entity !== Export::ENTITY_SHIPMENT && $entity !== Export::ENTITY_QUOTE && $entity !== Export::ENTITY_CUSTOMER && $entity !== Export::ENTITY_EERMA && $entity !== Export::ENTITY_AWRMA) {
            // Not available for shipments
            $fieldset->addField(
                'export_filter_status',
                'multiselect',
                [
                    'label' => __('%1 Status', ucfirst($entity)),
                    'name' => 'export_filter_status',
                    'values' => array_merge_recursive(
                        [['value' => '', 'label' => __('--- All statuses ---')]],
                        $this->exportStatus->toOptionArray(
                            $entity
                        )
                    ),
                    'note' => __('Export only %1s with status X. Hold down CTRL to select multiple.', $entity)
                ]
            );
        }

        if ($entity !== Export::ENTITY_QUOTE && $entity !== Export::ENTITY_CUSTOMER && $entity !== Export::ENTITY_EERMA && $entity !== Export::ENTITY_AWRMA && $entity !== Export::ENTITY_BOOSTRMA) {
            $fieldset = $form->addFieldset(
                'item_filters',
                ['legend' => __('Item Filters'), 'class' => 'fieldset-wide']
            );

            $fieldset->addField(
                'export_filter_product_type',
                'multiselect',
                [
                    'label' => __('Hidden Product Types'),
                    'name' => 'export_filter_product_type',
                    'values' => array_merge_recursive(
                        [['value' => '', 'label' => __('--- No hidden product types ---')]],
                        $this->productType->getOptions()
                    ),
                    'note' => __(
                        'The selected product types won\'t be exported and won\'t show up in the output format for this profile. You can still fetch information from the parent product in the XSL Template using the <i>parent_item/</i> node. '
                    )
                ]
            );
        }

        if ($entity !== Export::ENTITY_CUSTOMER && $entity !== Export::ENTITY_AWRMA && $entity !== Export::ENTITY_EERMA && $entity !== Export::ENTITY_BOOSTRMA) {
            $renderer = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Renderer\Fieldset::class);
            $renderer = $renderer->setTemplate(
                'Magento_CatalogRule::promo/fieldset.phtml'
            )->setNewChildUrl(
                $this->getUrl(
                    'xtento_orderexport/profile/newConditionHtml/form/rule_conditions_fieldset',
                    ['profile_id' => $model->getId()]
                )
            )->setNameInLayout('xtento_orderexport_profile_newConditionHtml_form_rule-conditions-fieldset');

            $fieldset = $form->addFieldset(
                'rule_conditions_fieldset',
                [
                    'legend' => __(
                        'Additional filters: Export %1 only if the following conditions are met',
                        $entity
                    ),
                ]
            )->setRenderer($renderer);

            $fieldset->addField(
                'conditions',
                'text',
                [
                    'name' => 'conditions',
                    'label' => __('Conditions'),
                    'title' => __('Conditions'),
                ]
            )->setRule($model)->setRenderer($this->conditions);
        }

        if ($entity == Export::ENTITY_ORDER) {
            $fieldset = $form->addFieldset('actions', ['legend' => __('Actions'), 'class' => 'fieldset-wide',]);

            // Only available for orders
            $fieldset->addField(
                'export_action_change_status',
                'select',
                [
                    'label' => __('Change %1 status after export', $entity),
                    'name' => 'export_action_change_status',
                    'values' => $this->allStatuses->toOptionArray(),
                    'note' => __('Change %1 status to X after exporting.', $entity)
                ]
            );
            $fieldset->addField(
                'export_action_add_comment',
                'text',
                [
                    'label' => __('Add comment to status history'),
                    'name' => 'export_action_add_comment',
                    'note' => __(
                        'Comment is added to the order status history after the order has been exported. Attention: This only works if the order status you\'re changing to is assigned to an order "state" at Stores > Order Status.'
                    )
                ]
            );
            $fieldset->addField(
                'export_action_invoice_order',
                'select',
                [
                    'label' => __('Invoice order after exporting'),
                    'name' => 'export_action_invoice_order',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'If enabled, after exporting, the order would be invoiced and the payment would be captured.'
                    )
                ]
            );
            $fieldset->addField(
                'export_action_invoice_notify',
                'select',
                [
                    'label' => __('Notify customer about invoice'),
                    'name' => 'export_action_invoice_notify',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'If "Invoice order after exporting" is enabled, the customer would receive an email after the invoice has been created.'
                    )
                ]
            );
            $fieldset->addField(
                'export_action_ship_order',
                'select',
                [
                    'label' => __('Ship order after exporting'),
                    'name' => 'export_action_ship_order',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __('If enabled, after exporting, the order would be shipped.')
                ]
            );
            $fieldset->addField(
                'export_action_ship_notify',
                'select',
                [
                    'label' => __('Notify customer about shipment'),
                    'name' => 'export_action_ship_notify',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __(
                        'If "Ship order after exporting" is enabled, the customer would receive an email after the shipment has been created.'
                    )
                ]
            );
            $fieldset->addField(
                'export_action_cancel_order',
                'select',
                [
                    'label' => __('Cancel order'),
                    'name' => 'export_action_cancel_order',
                    'values' => $this->yesNo->toOptionArray(),
                    'note' => __('If set to "Yes", this will cancel the order after the profile has been executed.')
                ]
            );
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Filters / Actions');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Filters / Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}