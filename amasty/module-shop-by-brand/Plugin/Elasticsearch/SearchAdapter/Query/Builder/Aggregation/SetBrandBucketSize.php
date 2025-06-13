<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Elasticsearch\SearchAdapter\Query\Builder\Aggregation;

use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation;

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
     * @param Aggregation $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuild(Aggregation $subject, $result)
    {
        $brandBucketName = $this->configProvider->getBrandAttributeCode() . '_bucket';
        if (isset($result['body']['aggregations'][$brandBucketName]['terms'])) {
            $result['body']['aggregations'][$brandBucketName]['terms']['size']
                = $this->configProvider->getBrandBucketSize();
        }

        return $result;
    }
}
