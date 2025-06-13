<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Client;

interface ClientInterface
{
    /**
     * @param array $query
     * @return array
     */
    public function query(array $query): array;

    /**
     * @param array $query
     * @return array
     * @throws \Amasty\ElasticSearch\Exception\Missing404Exception
     */
    public function search(array $query): array;

    /**
     * @param array $query
     * @return int
     * @throws \Amasty\ElasticSearch\Exception\Missing404Exception
     */
    public function count(array $query): int;

    /**
     * @param array $query
     * @return array
     */
    public function bulkQuery(array $query): array;

    /**
     * @param string $index
     * @return bool
     */
    public function isEmptyIndex(string $index): bool;

    /**
     * @param string $index
     * @param array $settings
     * @return void
     */
    public function createIndex(string $index, array $settings): void;

    /**
     * @param string $index
     * @return void
     */
    public function deleteIndex(string $index): void;

    /**
     * @param string $index
     * @return bool
     */
    public function indexExists(string $index): bool;

    /**
     * @param string $alias
     * @param string $index
     * @return bool
     * @throws \Amasty\ElasticSearch\Exception\NoNodesAvailableException
     */
    public function existsAlias(string $alias, string $index = ''): bool;

    /**
     * @param string $alias
     * @return array
     */
    public function getAlias(string $alias): array;

    /**
     * @param string $alias
     * @param string $newIndex
     * @return void
     */
    public function updateAlias(string $alias, string $newIndex): void;

    /**
     * @param array $params
     * @return void
     */
    public function updateAliases(array $params): void;

    /**
     * @param array $params
     * @return void
     */
    public function putMapping(array $params): void;

    /**
     * @return bool
     * @throws \Amasty\ElasticSearch\Exception\SystemPackageNotInstalled
     */
    public function ping(): bool;

    /**
     * Check if correct system package installed.
     * @return bool
     */
    public function isSystemPackageAvailable(): bool;

    /**
     * Get version of system package needed to install.
     * @return string
     */
    public function getNeededSystemPackageVersion(): string;

    /**
     * @return string
     */
    public function getSystemPackageName(): string;

    public function getCurrentEngineVersion(): string;
}
