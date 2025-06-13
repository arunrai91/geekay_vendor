<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\Elasticsearch5\Model\Adapter\BatchDataMapper;

use Amasty\Xsearch\Model\Elasticsearch\AdditionalDataMapper;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;

class ProductDataMapperPlugin
{
    /**
     * @var AdditionalDataMapper
     */
    private $additionalDataMapper;

    public function __construct(AdditionalDataMapper $additionalDataMapper)
    {
        $this->additionalDataMapper = $additionalDataMapper;
    }

    /**
     * Prepare index data for using in search engine metadata.
     *
     * Amasty_ShopBy have plugin for the same class and may change the result document
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ProductDataMapper $subject
     * @param array $documentData
     * @param array $documentDataInput
     * @param int|string $storeId
     * @param array $context
     * @return array
     */
    public function afterMap(
        $subject,
        array $documentData,
        array $documentDataInput,
        $storeId,
        $context = []
    ): array {
        return $this->additionalDataMapper->map($documentData, $storeId, $context);
    }
}
