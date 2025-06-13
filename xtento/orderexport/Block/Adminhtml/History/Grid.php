<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-08-22T10:46:34+00:00
 * File:          Block/Adminhtml/History/Grid.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\History;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->getRequest()->getParam('ajax_enabled', 0) == 1) {
            $this->setData('use_ajax', true);
            $this->setData('grid_url', $this->getUrl('*/history/grid', ['_current' => 1]));
        } else {
            $this->setData('use_ajax', false);
        }
    }

    protected function getFormMessages()
    {
        $formMessages = [
            [
                'type' => 'notice',
                'message' => __(
                    "Exported objects get logged here. You can see when an object was exported. Look up the execution log entry to see why. You can also delete objects here and have them re-exported if \"Export only new objects\" is set to \"Yes\"."
                )
            ]
        ];
        return $formMessages;
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->getParam('ajax')) {
            return parent::_toHtml();
        }
        return $this->_getFormMessages() . parent::_toHtml();
    }

    protected function _getFormMessages()
    {
        $html = '<div id="messages"><div class="messages">';
        foreach ($this->getFormMessages() as $formMessage) {
            $html .= '<div class="message message-' . $formMessage['type'] . ' ' . $formMessage['type'] . '"><div>' . $formMessage['message'] . '</div></div>';
        }
        $html .= '</div></div>';
        return $html;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getIndex() == 'increment_id') {
            $column->setFilterConditionCallback([$this, 'filterIncrementId']);
        }
        return parent::_addColumnFilterToCollection($column);
    }

    // @codingStandardsIgnoreStart
    protected function filterIncrementId($collection, $column)
    {
        // @codingStandardsIgnoreEnd
        if (!$value = trim($column->getFilter()->getValue())) {
            return;
        }

        $value = '%' . $value . '%';

        // addFieldToFilter is not able to handle or conditions from arrays in custom collections
        $sqlArr = [
            $this->getCollection()->getConnection()->quoteInto("order.increment_id LIKE ?", $value),
            $this->getCollection()->getConnection()->quoteInto("invoice.increment_id LIKE ?", $value),
            $this->getCollection()->getConnection()->quoteInto("shipment.increment_id LIKE ?", $value),
            $this->getCollection()->getConnection()->quoteInto("creditmemo.increment_id LIKE ?", $value),
        ];
        $conditionSql = '(' . join(') OR (', $sqlArr) . ')';
        $this->getCollection()->getSelect()->where($conditionSql, null, \Magento\Framework\DB\Select::TYPE_CONDITION);
    }
}