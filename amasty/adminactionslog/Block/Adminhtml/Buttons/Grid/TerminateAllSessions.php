<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Block\Adminhtml\Buttons\Grid;

use Amasty\AdminActionsLog\Block\Adminhtml\Buttons\GenericButton;

class TerminateAllSessions extends GenericButton
{
    public const ADMIN_RESOURCE = 'Amasty_AdminActionsLog::active_sessions';

    public function getButtonData(): array
    {
        if ($this->authorization->isAllowed(self::ADMIN_RESOURCE)) {
            $alertMessage = __('Are you sure you want to terminate all sessions?'
                . ' Your current session will also be terminated.');
            $onClick = sprintf(
                'deleteConfirm("%s", "%s")',
                $alertMessage,
                $this->getClearLogUrl()
            );

            return [
                'label' => __('Terminate All Sessions'),
                'class' => 'primary',
                'on_click' => $onClick,
                'sort_order' => 10,
            ];
        }

        return [];
    }

    public function getClearLogUrl(): string
    {
        return $this->getUrl('*/*/clear');
    }
}
