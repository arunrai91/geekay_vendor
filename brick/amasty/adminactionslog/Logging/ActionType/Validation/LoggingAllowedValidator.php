<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType\Validation;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Model\Admin\IsLoggingAllowed;

class LoggingAllowedValidator implements ActionValidatorInterface
{
    /**
     * @var IsLoggingAllowed
     */
    private $isLoggingAllowed;

    public function __construct(IsLoggingAllowed $isLoggingAllowed)
    {
        $this->isLoggingAllowed = $isLoggingAllowed;
    }

    public function isValid(MetadataInterface $metadata): bool
    {
        return $this->isLoggingAllowed->execute();
    }
}
