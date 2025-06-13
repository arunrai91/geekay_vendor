<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Adapter;

interface AdapterInterface
{
    /**
     * @param array $documentData
     * @param int $storeId
     * @param string $indexerId
     * @return array
     */
    public function prepareDocuments(array $documentData, int $storeId, string $indexerId): array;

    /**
     * @param array $documents
     * @param int $storeId
     * @param string $indexerId
     * @return void
     * @throws \Exception
     */
    public function saveDocuments(array $documents, int $storeId, string $indexerId): void;

    /**
     * @param array $documentIds
     * @param int $storeId
     * @param string $indexerId
     * @return void
     * @throws \Exception
     */
    public function deleteDocuments(array $documentIds, int $storeId, string $indexerId): void;

    /**
     * @param int $storeId
     * @param string $indexerId
     * @return void
     */
    public function cleanIndex(int $storeId, string $indexerId): void;

    /**
     * @param int $storeId
     * @param string $indexerId
     * @return void
     */
    public function checkIndex(int $storeId, string $indexerId): void;

    /**
     * @param int $storeId
     * @param string $indexerId
     * @return void
     */
    public function updateAlias(int $storeId, string $indexerId): void;

    /**
     * @return bool
     */
    public function ping(): bool;
}
