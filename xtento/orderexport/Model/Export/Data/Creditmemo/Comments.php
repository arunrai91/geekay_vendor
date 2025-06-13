<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T18:14:21+00:00
 * File:          Model/Export/Data/Creditmemo/Comments.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Creditmemo;

class Comments extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    public function getConfiguration()
    {
        return [
            'name' => 'Credit Memo Comments',
            'category' => 'Creditmemo',
            'description' => 'Export any comments added to credit memos, retrieved from the sales_flat_creditmemo_comment table.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO],
        ];
    }

    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreEnd
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['creditmemo_comments'];
        // Fetch fields to export
        $creditmemo = $collectionItem->getObject();

        if (!$this->fieldLoadingRequired('creditmemo_comments')) {
            return $returnArray;
        }

        if ($creditmemo) {
            $commentsCollection = $creditmemo->getCommentsCollection();
            if ($commentsCollection) {
                foreach ($commentsCollection->getItems() as $creditmemoComment) {
                    $this->writeArray = & $returnArray['creditmemo_comments'][];
                    $this->writeValue('comment', $creditmemoComment->getComment());
                    $this->writeValue('created_at', $creditmemoComment->getCreatedAt());
                }
            }
        }
        $this->writeArray = & $returnArray;
        // Done
        return $returnArray;
    }
}