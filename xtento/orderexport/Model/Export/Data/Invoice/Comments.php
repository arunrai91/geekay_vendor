<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T18:14:42+00:00
 * File:          Model/Export/Data/Invoice/Comments.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Invoice;

class Comments extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    public function getConfiguration()
    {
        return [
            'name' => 'Invoice Comments',
            'category' => 'Invoice',
            'description' => 'Export any comments added to invoices, retrieved from the sales_flat_invoice_comment table.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_INVOICE],
        ];
    }

    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreEnd
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['invoice_comments'];
        // Fetch fields to export
        $invoice = $collectionItem->getInvoice();

        if (!$this->fieldLoadingRequired('invoice_comments')) {
            return $returnArray;
        }

        if ($invoice) {
            $commentsCollection = $invoice->getCommentsCollection();
            if ($commentsCollection) {
                foreach ($commentsCollection->getItems() as $invoiceComment) {
                    $this->writeArray = & $returnArray['invoice_comments'][];
                    $this->writeValue('comment', $invoiceComment->getComment());
                    $this->writeValue('created_at', $invoiceComment->getCreatedAt());
                }
            }
        }
        $this->writeArray = & $returnArray;
        // Done
        return $returnArray;
    }
}