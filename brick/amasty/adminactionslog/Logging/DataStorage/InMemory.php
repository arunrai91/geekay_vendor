<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\DataStorage;

use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;

class InMemory implements ObjectDataStorageInterface
{
    /**
     * Data storage for entities data.
     * We are using spl_object_id to correctly identify objects.
     *
     * Data structure example:
     * [objectHashId.postfix] => [...]
     *
     * @var array
     */
    private $entityDataStorage = [];

    public function get(string $key): ?array
    {
        return $this->entityDataStorage[$key] ?? null;
    }

    public function set(string $key, array $data): void
    {
        $this->entityDataStorage[$key] = $data;
    }

    public function unset(string $key): void
    {
        unset($this->entityDataStorage[$key]);
    }

    public function isExists(string $key): bool
    {
        return isset($this->entityDataStorage[$key]);
    }
}
