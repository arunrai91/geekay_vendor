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

class CategoriesProcessor implements RelationInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function processRelation(AbstractModel $object): void
    {
        $entityId = (int)$object->getEntityId();
        $categories = explode(',', (string)$object->getData(BannerRuleInterface::BANNER_PRODUCT_CATEGORIES));
        $categories = array_map('strtolower', $categories);
        $categories = array_unique($categories);

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(BannerRule::RULE_CATEGORIES_TABLE);
        $connection->delete($tableName, ['entity_id = ?' => $entityId]);

        foreach ($categories as $category) {
            if (trim($category) !== '') {
                $data = [
                    'entity_id' => $entityId,
                    'banner_product_categories' => trim($category)
                ];

                $connection->insert(
                    $tableName,
                    $data
                );
            }
        }
    }
}
