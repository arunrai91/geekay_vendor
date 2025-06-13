<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Plugin\Framework\Model\ResourceModel\Db\VersionControl;

use Amasty\BannersLite\Model\BannerRule;
use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata;

class ModifyBannerMetadataPlugin
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetFields(Metadata $subject, array $result, DataObject $entity): array
    {
        if ($entity instanceof BannerRule) {
            $result['banner_product_categories'] = null;
            $result['banner_product_sku'] = null;
        }

        return $result;
    }
}
