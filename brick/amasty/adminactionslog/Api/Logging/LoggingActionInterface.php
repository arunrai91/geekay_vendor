<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Logging;

interface LoggingActionInterface
{
    /**
     * Perform logging action i.e. save product changes, save visit history, etc.
     */
    public function execute(): void;
}
