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

class RatingSummary implements DataMapperInterface
{
    public const FIELD_NAME = 'rating_summary';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Shopby\Model\Search\DataProvider\Product\RatingProvider
     */
    private $ratingProvider;

    public function __construct(
        \Amasty\Shopby\Model\Search\DataProvider\Product\RatingProvider $ratingProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->ratingProvider = $ratingProvider;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = []): array
    {
        return [self::FIELD_NAME => $this->ratingProvider->getProductRating((int) $entityId, (int) $storeId)];
    }

    public function isAllowed(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag('amshopby/rating_filter/enabled', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getFieldName(): string
    {
        return self::FIELD_NAME;
    }
}
