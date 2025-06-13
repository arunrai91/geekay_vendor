<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Magento\Framework\App\ObjectManager;

class RestoreValidator
{
    /**
     * @var RestoreValidatorProvider
     */
    private $restoreValidatorProvider;

    public function __construct(
        ?array $notRestorableCategories = null, // @deprecated
        RestoreValidatorProvider $restoreValidatorProvider = null
    ) {
        // OM for backward compatibility
        $this->restoreValidatorProvider = $restoreValidatorProvider
            ?? ObjectManager::getInstance()->get(RestoreValidatorProvider::class);
    }

    public function isValid(LogEntryInterface $logEntry): bool
    {
        $isValid = true;
        foreach ($this->restoreValidatorProvider->getValidators() as $validator) {
            if (!$validator->isValid($logEntry)) {
                $isValid = false;
                break;
            }
        }

        return $isValid;
    }
}
