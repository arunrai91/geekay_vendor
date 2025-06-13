<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-08-22T10:45:08+00:00
 * File:          Block/Adminhtml/Log/Grid.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Log;

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
            $this->setData('grid_url', $this->getUrl('*/log/grid', ['_current' => 1]));
        } else {
            $this->setData('use_ajax', false);
        }
    }

    protected function getFormMessages()
    {
        $formMessages = [
            [
                'type' => 'notice',
                'message' => __('All exports are logged here. Find failed exports or download exported files.')
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
}