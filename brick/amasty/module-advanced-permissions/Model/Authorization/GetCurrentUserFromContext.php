<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Api\Data\UserInterfaceFactory;
use Magento\User\Model\ResourceModel\User as UserResource;

class GetCurrentUserFromContext implements GetCurrentUserInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var UserInterfaceFactory
     */
    private $userFactory;

    /**
     * @var UserResource
     */
    private $userResource;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        UserContextInterface $userContext,
        UserInterfaceFactory $userFactory,
        UserResource $userResource,
        ?Registry $registry = null
    ) {
        $this->userContext = $userContext;
        $this->userFactory = $userFactory;
        $this->userResource = $userResource;
        $this->registry = $registry ?? ObjectManager::getInstance()->get(Registry::class);
    }

    public function execute(): ?UserInterface
    {
        $user = null;

        if ($this->registry->registry('isNonceSaved')) {
            return null;
        }

        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN) {
            $userId = $this->userContext->getUserId();
            if ($userId) {
                $user = $this->userFactory->create();
                $this->userResource->load($user, $userId);
            }
        }

        return $user;
    }
}
