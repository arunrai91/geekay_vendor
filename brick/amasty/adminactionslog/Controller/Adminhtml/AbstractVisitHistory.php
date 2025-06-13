<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class AbstractVisitHistory extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_AdminActionsLog::page_visit_history';
}
