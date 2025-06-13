<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Plugin\AdminNotification\Block\Grid\Renderer;

use Magento\AdminNotification\Block\Grid\Renderer\Notice as NativeNotice;
use Magento\Framework\DataObject;

class Notice
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRender(NativeNotice $subject, string $result, DataObject $row): string
    {
        $amastyLogo = $amastyImage = '';
        if ($row->getData('is_amasty')) {
            if ($row->getData('image_url')) {
                $amastyImage = ' style="background: url(' . $row->getData("image_url") . ') no-repeat;"';
            } else {
                $amastyLogo = ' amasty-grid-logo';
            }
        }

        return '<div class="ambase-grid-message' . $amastyLogo . '"' . $amastyImage . '>' . $result . '</div>';
    }
}
