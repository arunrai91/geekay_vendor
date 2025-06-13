<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-04-18T18:31:43+00:00
 * File:          Block/Adminhtml/Profile/Edit/Tab/Output.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Profile\Edit\Tab;

class Output extends \Xtento\OrderExport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Xtento\OrderExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    protected $assetSource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Xtento\OrderExport\Helper\Entity $entityHelper
     * @param \Magento\Framework\View\Asset\Source $assetSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Xtento\OrderExport\Helper\Entity $entityHelper,
        \Magento\Framework\View\Asset\Source $assetSource,
        array $data = []
    ) {
        $this->entityHelper = $entityHelper;
        $this->assetSource = $assetSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function getFormMessages()
    {
        $formMessages = [];
        $formMessages[] = [
            'type' => 'notice',
            'message' => __(
                'The XSL Template "translates" the internal Magento database format into your required output format. You can find more information about XSL Templates in our <a href="http://support.xtento.com/wiki/Magento_2_Extensions:Magento_Order_Export_Module" target="_blank">support wiki</a>. If you don\'t want to create the XSL Template yourself, please refer to our <a href="http://www.xtento.com/magento-services/xsl-template-creation-service.html" target="_blank">XSL Template Creation Service</a>.'
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
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $this->setForm($form);
        $this->setTemplate('Xtento_OrderExport::profile/output.phtml');
        return parent::_prepareForm();
    }

    public function getTestIncrementId()
    {
        $profile = $this->_coreRegistry->registry('orderexport_profile');
        if (!$profile->getEntity()) {
            return '';
        }
        $testId = $profile->getTestId();
        if (!$testId || $testId == 0) {
            return $this->entityHelper->getLastIncrementId($profile->getEntity());
        } else {
            return $testId;
        }
    }

    public function getXslTemplate()
    {
        return htmlspecialchars($this->_coreRegistry->registry('orderexport_profile')->getXslTemplate(), ENT_NOQUOTES);
    }

    public function getRegistry()
    {
        return $this->_coreRegistry;
    }

    public function getJs($filename)
    {
        $url = $this->_assetRepo->createAsset(
            'Xtento_OrderExport::js/' . $filename,
            ['_secure' => $this->getRequest()->isSecure()]
        )->getUrl();
        return $url;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Output Format');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Output Format');
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