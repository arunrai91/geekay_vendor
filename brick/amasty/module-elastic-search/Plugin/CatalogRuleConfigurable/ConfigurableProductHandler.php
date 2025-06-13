<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Plugin\CatalogRuleConfigurable;

use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\ConfigurableProductHandler
    as CatalogRuleConfigurableHandler;

/**
 * Disabling addVariationsToProductRule original plugin by using same name, and calling it by condition
 */
class ConfigurableProductHandler
{
    /**
     * @var CatalogRuleConfigurableHandler
     */
    private $parentHandler;

    public function __construct(
        CatalogRuleConfigurableHandler $parentHandler
    ) {
        $this->parentHandler = $parentHandler;
    }

    /**
     * @see \Magento\CatalogRule\Model\Rule::getMatchingProductIds()
     *
     * @param Rule $rule
     * @param \Closure $proceed
     * @return array
     */
    public function aroundGetMatchingProductIds(Rule $rule, \Closure $proceed): array
    {
        $productIds = null;

        if (!$rule->getAmastyRelevanceRule()) {
            if (method_exists($this->parentHandler, 'aroundGetMatchingProductIds')) {
                $productIds = $this->parentHandler->aroundGetMatchingProductIds($rule, $proceed);
            } elseif (method_exists($this->parentHandler, 'afterGetMatchingProductIds')) {
                $productIds = $proceed();
                $productIds = $this->parentHandler->afterGetMatchingProductIds($rule, $productIds);
            }
        }

        if (!$productIds) {
            $productIds = $proceed();
        }

        return $productIds;
    }
}
