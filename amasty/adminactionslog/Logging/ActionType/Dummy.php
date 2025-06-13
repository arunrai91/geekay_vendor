<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType;

use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;

class Dummy implements LoggingActionInterface
{
    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function execute(): void
    {
    }
}
