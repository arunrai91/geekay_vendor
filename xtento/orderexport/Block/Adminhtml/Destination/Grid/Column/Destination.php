<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-02-25T14:51:47+00:00
 * File:          Block/Adminhtml/Destination/Grid/Column/Destination.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Destination\Grid\Column;

class Destination extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @var \Xtento\OrderExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * Destination constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileFactory = $profileFactory;
    }

    protected function getProfile()
    {
        return $this->profileFactory->create()->load(
            $this->getRequest()->getParam('id')
        );
    }

    public function getValues()
    {
        $array = [];
        foreach (explode("&", $this->getProfile()->getDestinationIds()) as $key => $destinationId) {
            if ($destinationId === '') continue;
            $array[] = ['label' => $destinationId, 'value' => $destinationId];
        }
        return $array;
    }
}
