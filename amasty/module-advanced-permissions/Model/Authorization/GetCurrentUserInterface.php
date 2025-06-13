<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\Authorization;

use Magento\User\Api\Data\UserInterface;

interface GetCurrentUserInterface
{
    /**
     * @return UserInterface|null
     */
    public function execute(): ?UserInterface;
}
