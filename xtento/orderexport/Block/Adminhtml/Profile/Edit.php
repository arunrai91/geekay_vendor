<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-01-08T13:13:35+00:00
 * File:          Block/Adminhtml/Profile/Edit.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Profile;

use Xtento\OrderExport\Model\Export;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Xtento\OrderExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\OrderExport\Helper\Entity $entityHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\OrderExport\Helper\Entity $entityHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->entityHelper = $entityHelper;
        parent::__construct($context, $data);
    }

    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Xtento_OrderExport';
        $this->_controller = 'adminhtml_profile';
        parent::_construct();

        if ($this->registry->registry('orderexport_profile')->getId()) {
            $this->buttonList->add(
                'duplicate_button',
                [
                    'label' => __('Duplicate Profile'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('*/*/duplicate', ['_current' => true]) . '\')',
                    'class' => 'add',
                ],
                0
            );

            $this->buttonList->add(
                'load_default_template',
                [
                    'label' => __('Load Sample Profile'),
                    'onclick' => 'window.defaultTemplateModal.open()'
                ],
                -200
            );

            $this->buttonList->update('save', 'label', __('Save Profile'));
            $this->buttonList->update('delete', 'label', __('Delete Profile'));
            $this->buttonList->remove('reset');
        } else {
            $this->buttonList->remove('delete');
            $this->buttonList->remove('save');
        }

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save primary',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ]
        );
    }
}