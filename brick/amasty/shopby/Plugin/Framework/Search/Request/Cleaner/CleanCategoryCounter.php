<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Framework\Search\Request\Cleaner;

use Amasty\Shopby\Plugin\Framework\Search\Request\Registry;
use Magento\Framework\Search\Request\Cleaner;

/**
 * Additional request cleaning. Removing not used aggregations for performance.
 */
class CleanCategoryCounter
{
    private const DEFAULT_REQUEST_NAME = 'catalog_view_container';

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Cleaner $subject
     * @param array $requestData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterClean(Cleaner $subject, array $requestData): array
    {
        if ($requestData['query'] === self::DEFAULT_REQUEST_NAME
            && $this->registry->isAdditionalCleaningAllowed()
        ) {
            foreach ($requestData['aggregations'] as $name => $aggregation) {
                if ($name !== 'category_bucket') {
                    unset($requestData['aggregations'][$name]);
                }
            }
        }

        return $requestData;
    }
}
