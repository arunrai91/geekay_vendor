<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T18:47:07+00:00
 * File:          Controller/Adminhtml/Profile/FieldsXml.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Profile;

class FieldsXml extends \Xtento\OrderExport\Controller\Adminhtml\Profile
{
    /**
     * @var \Xtento\OrderExport\Model\Output\XmlFactory
     */
    protected $outputXmlFactory;

    /**
     * FieldsXml constructor.
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
     * @param \Xtento\OrderExport\Model\Output\XmlFactory $outputXmlFactory
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
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\Output\XmlFactory $outputXmlFactory
    ) {
        parent::__construct(
            $context,
            $moduleHelper,
            $cronHelper,
            $profileCollectionFactory,
            $registry,
            $escaper,
            $scopeConfig,
            $dateFilter,
            $entityHelper,
            $profileFactory
        );
        $this->outputXmlFactory = $outputXmlFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('profile_id');
        $model = $this->profileFactory->create()->load($id);
        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This profile no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            return $resultRedirect->setPath('*/*/');
        }
        $this->registry->unregister('orderexport_profile');
        $this->registry->register('orderexport_profile', $model);

        $orderExport = $this->_objectManager->create(
            '\Xtento\OrderExport\Model\Export\Entity\\' . ucfirst($model->getEntity())
        );
        $orderExport->setProfile($model);
        $orderExport->setShowEmptyFields(1);
        $orderExport->setCollectionFilters(
            [['increment_id' => ['in' => explode(",", $this->getRequest()->getParam('test_id'))]]]
        );
        $returnArray = $orderExport->runExport();
        $xmlData = $this->outputXmlFactory->create()->setProfile($model)->convertData($returnArray);

        if (empty($xmlData)) {
            $xmlData[0] = '<objects></objects>';
        }
        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $resultPage->setHeader('Content-Type', 'text/xml');
        $resultPage->setContents($xmlData[0]);
        return $resultPage;
    }
}
