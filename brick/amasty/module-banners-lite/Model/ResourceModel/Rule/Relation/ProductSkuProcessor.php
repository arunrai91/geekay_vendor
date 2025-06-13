<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model\ResourceModel\Rule\Relation;

use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\ResourceModel\BannerRule;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

class ProductSkuProcessor implements RelationInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param AbstractModel $object
     * @return void
     */
    public function processRelation(AbstractModel $object): void
    {
        $entityId = (int)$object->getEntityId();
        $skuString = (string)$object->getData(BannerRuleInterface::BANNER_PRODUCT_SKU);
        $skus = $this->extractUniqueSkus($skuString);

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(BannerRule::RULE_PRODUCT_SKU_TABLE);
        $connection->delete($tableName, ['entity_id = ?' => $entityId]);

        foreach ($skus as $originalSku) {
            $dataToInsert[] = [
                'entity_id' => $entityId,
                'banner_product_sku' => $originalSku
            ];
        }

        if (!empty($dataToInsert)) {
            $connection->insertMultiple(
                $tableName,
                $dataToInsert
            );
        }
    }

    /**
     * @param $skuString
     * @return array
     */
    private function extractUniqueSkus(string $skuString): array
    {
        $inputSkus = explode(',', $skuString);
        $skus = [];
        foreach ($inputSkus as $sku) {
            if ($trimmedSku = trim($sku)) {
                $skus[strtolower($trimmedSku)] = $trimmedSku;
            }
        }

        return $skus;
    }
}
