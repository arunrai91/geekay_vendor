<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\Product;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\EntityBuilderInterface;
use Amasty\ElasticSearch\Model\Di\Wrapper as DiWrapper;
use Amasty\ElasticSearch\Model\Indexer\Data\Product\OutofStockDataMapper;
use Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\Product;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;

/**
 * Provide is_out_of_stock field.
 * Need because we have our data mapper, which different with magento data mapper.
 *
 * @see \Magento\InventoryElasticsearch\Plugin\Model\Adapter\FieldMapper\AdditionalFieldMapperPlugin
 */
class OutofStockData implements EntityBuilderInterface
{
    /**
     * @var DiWrapper|StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    public function __construct(DiWrapper $stockByWebsiteIdResolver)
    {
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
    }

    /**
     * @return array
     */
    public function buildEntityFields()
    {
        if (!$this->stockByWebsiteIdResolver->canCreateObject()) {
            return [];
        }
        return [OutofStockDataMapper::OUT_OF_STOCK_FIELD => ['type' => Product::ATTRIBUTE_TYPE_INT]];
    }
}
