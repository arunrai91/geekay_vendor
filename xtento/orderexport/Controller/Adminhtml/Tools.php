<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T18:47:08+00:00
 * File:          Controller/Adminhtml/Tools.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml;

abstract class Tools extends \Xtento\OrderExport\Controller\Adminhtml\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Xtento\OrderExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\OrderExport\Model\DestinationFactory
     */
    protected $destinationFactory;

    /**
     * @var \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface
     */
    protected $requestData;

    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * Tools constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Model\DestinationFactory $destinationFactory
     * @param \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\DestinationFactory $destinationFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        \Xtento\XtCore\Helper\Utils $utilsHelper
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $scopeConfig);
        $this->profileFactory = $profileFactory;
        $this->destinationFactory = $destinationFactory;
        $this->requestData = $requestData;
        $this->utilsHelper = $utilsHelper;
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Xtento_OrderExport::tools');
    }

    /**
     * @param $resultPage \Magento\Backend\Model\View\Result\Page
     */
    protected function updateMenu($resultPage)
    {
        $resultPage->setActiveMenu('Xtento_OrderExport::tools');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Tools'), __('Tools'));
        $resultPage->getConfig()->getTitle()->prepend(__('Sales Export - Tools'));
    }
}
