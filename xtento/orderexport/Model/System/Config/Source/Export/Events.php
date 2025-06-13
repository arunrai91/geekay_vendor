<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T12:45:36+00:00
 * File:          Model/System/Config/Source/Export/Events.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\System\Config\Source\Export;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Events implements ArrayInterface
{
    /**
     * @var \Xtento\OrderExport\Observer\AbstractEventObserver
     */
    protected $eventObserver;

    /**
     * Events constructor.
     * @param \Xtento\OrderExport\Observer\AbstractEventObserver $eventObserver
     */
    public function __construct(\Xtento\OrderExport\Observer\AbstractEventObserver $eventObserver)
    {
        $this->eventObserver = $eventObserver;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray($entity = false)
    {
        $optionArray = [];
        $events = $this->eventObserver->getEvents($entity);
        foreach ($events as $entityEvents) {
            foreach ($entityEvents as $eventId => $eventOptions) {
                $optionArray[$eventId] = $eventOptions['label'];
            }
        }
        return $optionArray;
    }
}
