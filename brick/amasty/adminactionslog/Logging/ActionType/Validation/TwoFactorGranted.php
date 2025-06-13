<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType\Validation;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Model\Di\Wrapper as TfaSession;
use Magento\Framework\Module\Manager;
use Magento\TwoFactorAuth\Api\TfaSessionInterface;

class TwoFactorGranted implements ActionValidatorInterface
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var TfaSessionInterface
     */
    private $tfaSession;

    public function __construct(
        Manager $moduleManager,
        TfaSession $tfaSession
    ) {
        $this->moduleManager = $moduleManager;
        $this->tfaSession = $tfaSession;
    }

    /**
     * Stateless session validator because TFA access can be changed during request processing.
     * @see \Magento\TwoFactorAuth\Model\TfaSession::grantAccess
     *
     * @param MetadataInterface $metadata
     * @return bool
     */
    public function isValid(MetadataInterface $metadata): bool
    {
        return $this->moduleManager->isEnabled('Magento_TwoFactorAuth')
            ? (bool)$this->tfaSession->isGranted()
            : true;
    }
}
