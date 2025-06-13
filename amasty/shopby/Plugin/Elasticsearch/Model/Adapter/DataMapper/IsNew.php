<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper;

use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapperInterface;
use Magento\Store\Model\ScopeInterface;

class IsNew implements DataMapperInterface
{
    public const FIELD_NAME = 'am_is_new';
    public const DOCUMENT_FIELD_NAME = 'news_from_date';
    public const INDEX_DOCUMENT = 'document';

    /**
     * @var array
     */
    private $newProductIds = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Shopby\Model\Search\DataProvider\Product\IsNewProvider
     */
    private $isNewProvider;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Shopby\Model\Search\DataProvider\Product\IsNewProvider $isNewProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->isNewProvider = $isNewProvider;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = []): array
    {
        return [
            self::FIELD_NAME => (int) $this->isNewProvider->isProductNew(
                (int) $entityId,
                (int) $storeId
            )
        ];
    }

    public function isAllowed(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'amshopby/am_is_new_filter/enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFieldName(): string
    {
        return self::FIELD_NAME;
    }
}
