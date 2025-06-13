<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Model\Authorization;

use Amasty\Rolepermissions\Model\Authorization\GetCurrentUserFromContext;
use Magento\Framework\Registry;
use Magento\User\Api\Data\UserInterface;

class SkipGetCurrentUserFromContext
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function beforeExecute(GetCurrentUserFromContext $subject): void
    {
        $this->registry->register('isNonceSaved', true);
    }

    public function afterExecute(GetCurrentUserFromContext $subject, ?UserInterface $result): ?UserInterface
    {
        $this->registry->unregister('isNonceSaved');

        return $result;
    }
}
