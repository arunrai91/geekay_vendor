<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\ElasticSearch\Model\Search\GetRequestQuery\GetAggregations;

use Amasty\ElasticSearch\Model\Search\GetRequestQuery\GetAggregations;
use Amasty\ShopbyBrand\Model\ConfigProvider;

class SetBrandBucketSize
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(GetAggregations $subject, array $result): array
    {
        $brandBucketName = $this->configProvider->getBrandAttributeCode() . '_bucket';
        if (isset($result[$brandBucketName]['terms'])) {
            $result[$brandBucketName]['terms']['size'] = $this->configProvider->getBrandBucketSize();
        }

        return $result;
    }
}
