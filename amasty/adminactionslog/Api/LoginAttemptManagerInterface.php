<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api;

interface LoginAttemptManagerInterface
{
    public function saveAttempt(?string $username, int $status): void;

    public function clear(int $period = null): void;
}
