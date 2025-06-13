<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Data\External;

use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperInterface;
use Amasty\ElasticSearch\Api\RelevanceRuleRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class RelevanceBoostDataMapper implements DataMapperInterface
{
    public const DEFAULT_RELEVANCE = 1.;
    public const ATTRIBUTE_NAME = 'amasty_product_relevance';

    /**
     * @var RelevanceRuleRepositoryInterface
     */
    private $relevanceRuleRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        RelevanceRuleRepositoryInterface $relevanceRuleRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->relevanceRuleRepository = $relevanceRuleRepository;
        $this->storeManager = $storeManager;
    }

    public function map(array $indexData, $storeId, array $context = []): array
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $productIds = array_keys($indexData);
        $productBoostData = $this->relevanceRuleRepository->getProductBoostMultipliers($productIds, (int) $websiteId);
        $indexBoostData = [];

        foreach ($indexData as $productId => $productData) {
            $indexBoostData[$productId] = [
                self::ATTRIBUTE_NAME => $productBoostData[$productId] ?? self::DEFAULT_RELEVANCE
            ];
        }

        return $indexBoostData;
    }
}
