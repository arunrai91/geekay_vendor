<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class History extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_Acart::acart_history';
}
