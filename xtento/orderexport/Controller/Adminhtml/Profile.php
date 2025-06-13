<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T18:47:08+00:00
 * File:          Controller/Adminhtml/Profile.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml;

abstract class Profile extends \Xtento\OrderExport\Controller\Adminhtml\Action
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
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Xtento\OrderExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * Profile constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Xtento\OrderExport\Helper\Entity $entityHelper
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Xtento\OrderExport\Helper\Entity $entityHelper,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $scopeConfig);
        $this->registry = $registry;
        $this->entityHelper = $entityHelper;
        $this->escaper = $escaper;
        $this->dateFilter = $dateFilter;
        $this->profileFactory = $profileFactory;
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Xtento_OrderExport::profile');
    }

    /**
     * @param $resultPage \Magento\Backend\Model\View\Result\Page
     */
    protected function updateMenu($resultPage)
    {
        $resultPage->setActiveMenu('Xtento_OrderExport::profiles');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Export Profiles'), __('Export Profiles'));
        $resultPage->getConfig()->getTitle()->prepend(__('Sales Export - Profiles'));
    }
}
