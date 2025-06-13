<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring\RestoreValidator;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ScopeValidator implements RestoreValidatorInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function isValid(LogEntryInterface $logEntry): bool
    {
        try {
            $this->storeManager->getStore((int)$logEntry->getStoreId());
            return true;
        } catch (NoSuchEntityException $exception) {
            return false;
        }
    }
}
