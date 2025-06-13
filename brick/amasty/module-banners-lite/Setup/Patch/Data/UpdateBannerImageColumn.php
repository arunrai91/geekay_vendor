<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Setup\Patch\Data;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Model\ResourceModel\Banner;
use Amasty\Base\Model\Serializer;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;

class UpdateBannerImageColumn implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Serializer $serializer
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->serializer = $serializer;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $updatedImages = $this->prepareImagesToUpdate();
        if (!empty($updatedImages)) {
            $connection = $this->moduleDataSetup->getConnection();
            $connection->insertOnDuplicate($this->moduleDataSetup->getTable(Banner::TABLE_NAME), $updatedImages);
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    private function prepareImagesToUpdate(): array
    {
        $connection = $this->moduleDataSetup->getConnection();
        $tableName = $this->moduleDataSetup->getTable(Banner::TABLE_NAME);
        $select = $connection->select()->from(
            $tableName,
            [BannerInterface::ENTITY_ID, BannerInterface::BANNER_IMAGE]
        );

        $updatedImages = [];
        foreach (array_filter($connection->fetchPairs($select)) as $entityId => $bannerImage) {
            $imageArray = $this->serializer->unserialize($bannerImage);
            if (isset($imageArray[0]['name'])) {
                $updatedImages[] = [
                    BannerInterface::ENTITY_ID => $entityId,
                    BannerInterface::BANNER_IMAGE => $imageArray[0]['name']
                ];
            }
        }

        return $updatedImages;
    }
}
