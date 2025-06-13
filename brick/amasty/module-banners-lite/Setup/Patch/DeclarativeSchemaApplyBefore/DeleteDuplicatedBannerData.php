<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Model\ImageProcessor;
use Amasty\BannersLite\Model\ResourceModel\Banner;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class DeleteDuplicatedBannerData implements PatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private SchemaSetupInterface $schemaSetup;

    /**
     * @var ImageProcessor
     */
    private ImageProcessor $imageProcessor;

    public function __construct(
        SchemaSetupInterface $schemaSetup,
        ImageProcessor $imageProcessor
    ) {
        $this->schemaSetup = $schemaSetup;
        $this->imageProcessor = $imageProcessor;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): DeleteDuplicatedBannerData
    {
        $connection = $this->schemaSetup->getConnection();
        $bannerDataTable = $this->schemaSetup->getTable(Banner::TABLE_NAME);

        if (!$connection->isTableExists($bannerDataTable)) {
            return $this;
        }

        $select = $connection->select()
            ->from($bannerDataTable, [BannerInterface::ENTITY_ID, BannerInterface::BANNER_IMAGE])
            ->where(
                BannerInterface::ENTITY_ID . ' NOT IN (?)',
                $connection->select()
                    ->from(
                        $bannerDataTable,
                        ['max_entity_id' => new \Zend_Db_Expr('MAX(' . BannerInterface::ENTITY_ID . ')')]
                    )
                    ->group([BannerInterface::SALESRULE_ID, BannerInterface::BANNER_TYPE])
            );

        $duplicateData = $connection->fetchPairs($select);

        if (!empty($duplicateData)) {
            $connection->delete(
                $bannerDataTable,
                [BannerInterface::ENTITY_ID . ' IN (?)' => array_keys($duplicateData)]
            );
            foreach ($duplicateData as $image) {
                try {
                    $this->imageProcessor->deleteImage((string)$image);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return $this;
    }
}
