<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\DataStorage;

use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Magento\Framework\FlagManager;

class FlagTable implements ObjectDataStorageInterface
{
    public const FLAG_CODE_PREFIX = 'amasty_audit_';

    /**
     * @var FlagManager
     */
    private $flagManager;

    public function __construct(FlagManager $flagManager)
    {
        $this->flagManager = $flagManager;
    }

    public function get(string $key): ?array
    {
        $storedData = $this->flagManager->getFlagData(self::FLAG_CODE_PREFIX . $key);

        return is_array($storedData) || $storedData === null ? $storedData : [$storedData];
    }

    public function set(string $key, array $data): void
    {
        $this->flagManager->saveFlag(self::FLAG_CODE_PREFIX . $key, $data);
    }

    public function unset(string $key): void
    {
        $this->flagManager->deleteFlag(self::FLAG_CODE_PREFIX . $key);
    }

    public function isExists(string $key): bool
    {
        return $this->flagManager->getFlagData(self::FLAG_CODE_PREFIX . $key) !== null;
    }
}
