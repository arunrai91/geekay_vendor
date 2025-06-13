<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Elasticsearch;

class AdditionalDataMapper
{
    /**
     * @var array
     */
    private $dataMappers;

    public function __construct(array $dataMappers = [])
    {

        $this->dataMappers = $dataMappers;
    }

    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map(array $documentData, $storeId, array $context = []): array
    {
        foreach ($this->dataMappers as $dataMapper) {
            $additionalData = $dataMapper->map($documentData, $storeId, $context);
            $documentData = $this->mergeData($additionalData, $documentData);
        }

        return $documentData;
    }

    private function mergeData(array $additionalData, array $documentData): array
    {
        foreach ($additionalData as $entityId => $entityData) {
            if (isset($documentData[$entityId])) {
                $documentData[$entityId] = $entityData + $documentData[$entityId];
            } else {
                $documentData[$entityId] = $entityData;
            }
        }

        return $documentData;
    }
}
