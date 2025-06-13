<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-09-17T12:56:43+00:00
 * File:          Helper/ExportService.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Helper;

use Magento\Framework\Registry;

class ExportService extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Xtento\OrderExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\OrderExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * ExportService constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Registry $registry
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Model\ExportFactory $exportFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Registry $registry,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\ExportFactory $exportFactory
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->profileFactory = $profileFactory;
        $this->exportFactory = $exportFactory;
    }

    /**
     * Use this function to export an $object (order, invoice, ...) for a specified $profileId
     *
     * Warning: Does not check profile filters such as date range, whether automatic exports are enabled yes/no, etc. Use it to force the export of an object.
     *
     * @param $profileId
     * @param $object
     *
     * @return bool
     */
    public function exportObject($profileId, $object)
    {
        $profile = $this->profileFactory->create()->load($profileId);
        if (!$profile->getId() || !$profile->getEnabled()) {
            return false;
        }
        $exportModel = $this->exportFactory->create()->setProfile($profile);
        $filters = [];
        if ($exportModel->eventExport($filters, $object)) {
            $this->registry->registry('orderexport_log')->setExportEvent('xtento_orderexport_helper_exportservice_exportobject')->save();
            return $this->registry->registry('orderexport_log');
        }
        return false;
    }
}
