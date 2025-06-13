<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-02-29T14:40:36+00:00
 * File:          Block/Adminhtml/Profile/Edit/Tabs.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Profile\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\OrderExport\Helper\Entity $entityHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        \Xtento\OrderExport\Helper\Entity $entityHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->entityHelper = $entityHelper;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Export Profile'));
        if ($this->registry->registry('orderexport_profile')->getId()) {
            $this->setTitle(
                __(
                    '%1 Export Profile',
                    $this->entityHelper->getEntityName($this->registry->registry('orderexport_profile')->getEntity())
                )
            );
        } else {
            $this->setTitle(__('Export Profile'));
        }
    }

    public function addTab($tabId, $tab)
    {
        if ($tabId == 'general' || $this->registry->registry('orderexport_profile')->getId()) {
            return parent::addTab($tabId, $tab);
        } else {
            return $this;
        }
    }
}