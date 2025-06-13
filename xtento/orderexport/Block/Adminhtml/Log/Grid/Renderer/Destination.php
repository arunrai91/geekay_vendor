<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T15:07:11+00:00
 * File:          Block/Adminhtml/Log/Grid/Renderer/Destination.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Log\Grid\Renderer;

class Destination extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public static $destinations = [];

    /**
     * @var \Xtento\OrderExport\Model\DestinationFactory
     */
    protected $destinationFactory;

    /**
     * @var \Xtento\OrderExport\Model\System\Config\Source\Destination\Type
     */
    protected $destinationSource;

    /**
     * Destination constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Xtento\OrderExport\Model\DestinationFactory $destinationFactory
     * @param \Xtento\OrderExport\Model\System\Config\Source\Destination\Type $destinationSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Xtento\OrderExport\Model\DestinationFactory $destinationFactory,
        \Xtento\OrderExport\Model\System\Config\Source\Destination\Type $destinationSource,
        array $data = []
    ) {
        $this->destinationFactory = $destinationFactory;
        $this->destinationSource = $destinationSource;
        parent::__construct($context, $data);
    }

    /**
     * Render log
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $destinationIds = $row->getDestinationIds();
        $destinationText = "";
        if (empty($destinationIds)) {
            return __('No destination selected. Enable in the "Export Destinations" tab of the profile.');
        }
        foreach (explode("&", $destinationIds) as $destinationId) {
            if (!empty($destinationId) && is_numeric($destinationId)) {
                if (!isset(self::$destinations[$destinationId])) {
                    $destination = $this->destinationFactory->create()->load(
                        $destinationId
                    );
                    self::$destinations[$destinationId] = $destination;
                } else {
                    $destination = self::$destinations[$destinationId];
                }
                if ($destination->getId()) {
                    $destinationText .= $destination->getName() . " (" . $this->destinationSource->getName($destination->getType()) . ")<br>";
                }
            }
        }
        return $destinationText;
    }
}
