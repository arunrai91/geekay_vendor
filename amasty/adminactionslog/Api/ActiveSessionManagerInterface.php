<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api;

interface ActiveSessionManagerInterface
{
    public function initNew(): void;

    public function update(): void;

    public function terminate(string $sessionId = null): void;

    public function getInactiveSessions(): array;

    public function terminateAll(): void;
}
