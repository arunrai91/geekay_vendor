<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-02-25T14:30:56+00:00
 * File:          Model/System/Config/Source/Export/Profile.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\System\Config\Source\Export;

class Profile
{
    /**
     * @var \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * @param \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     */
    public function __construct(
        \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
    ) {
        $this->profileCollectionFactory = $profileCollectionFactory;
    }

    public function toOptionArray($all = false, $entity = false, $getLastExportedId = false)
    {
        $profileCollection = $this->profileCollectionFactory->create();
        if (!$all) {
            $profileCollection->addFieldToFilter('enabled', 1);
            $profileCollection->addFieldToFilter('manual_export_enabled', 1);
        }
        if ($entity) {
            $profileCollection->addFieldToFilter('entity', $entity);
        }
        $profileCollection->getSelect()->order('entity ASC');
        $returnArray = [];
        foreach ($profileCollection as $profile) {
            $lastExportedId = '';
            if ($getLastExportedId) {
                $lastExportedId = $profile->getLastExportedIncrementId();
            }
            $returnArray[] = [
                'profile' => $profile,
                'value' => $profile->getId(),
                'label' => $profile->getName(),
                'entity' => $profile->getEntity(),
                'last_exported_increment_id' => $lastExportedId
            ];
        }
        if (empty($returnArray)) {
            $returnArray[] = [
                'profile' => new \Magento\Framework\DataObject(),
                'value' => '',
                'label' => __(
                    'No profiles available. Add and enable export profiles for the %1 entity first.',
                    $entity
                ),
                'entity' => '',
                'last_exported_increment_id' => ''
            ];
        }
        return $returnArray;
    }
}
