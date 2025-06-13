<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Logging;

interface ObjectDataStorageInterface
{
    /**
     * Method to retrieve stored object data.
     *
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array;

    /**
     * Method to store object's data in storage.
     *
     * @param string $key
     * @param array $data
     */
    public function set(string $key, array $data): void;

    /**
     * Method to remove object's data from storage.
     *
     * @param string $key
     */
    public function unset(string $key): void;

    /**
     * Method to to check is data persists in storage.
     *
     * @param string $key
     * @return bool
     */
    public function isExists(string $key): bool;
}
