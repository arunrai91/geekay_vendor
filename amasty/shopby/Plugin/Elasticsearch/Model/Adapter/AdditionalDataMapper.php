<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter;

class AdditionalDataMapper
{
    /**
     * @var DataMapperInterface[]
     */
    private $dataMappers = [];

    /**
     * AdditionalDataMapper constructor.
     * @param array $dataMappers
     */
    public function __construct(array $dataMappers = [])
    {
        $this->dataMappers = $dataMappers;
    }

    /**
     * Prepare index data for using in search engine metadata.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param $subject
     * @param array $document
     * @param $productId
     * @param array $indexData
     * @param $storeId
     * @param array $context
     * @return array
     */
    public function afterMap(
        $subject,
        array $document,
        $productId,
        array $indexData,
        $storeId,
        $context = []
    ): array {
        $context['document'] = $document;
        foreach ($this->dataMappers as $mapper) {
            if ($mapper instanceof DataMapperInterface
                && $mapper->isAllowed()
                && !isset($document[$mapper->getFieldName()])
            ) {
                // @codingStandardsIgnoreLine
                $document = array_merge($document, $mapper->map($productId, $indexData, $storeId, $context));
            }
        }

        return $document;
    }
}
