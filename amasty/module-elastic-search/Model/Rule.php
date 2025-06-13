<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model;

use Amasty\ElasticSearch\Ui\RelevanceRule\DataProvider\Product\Filter\RuleConditionFilter;
use Magento\CatalogRule\Model\Rule as CatalogRule;

/**
 * Exclude non-validated products to avoid going through the cycle of websites and then products twice
 * @see RuleConditionFilter::getMatchedProductsIds()
 */
class Rule extends CatalogRule
{
    public function callbackValidateProduct($args): void
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $websites = $this->_getWebsitesMap();
        $productId = $product->getId();

        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);

            if ($this->getConditions()->validate($product)) {
                $this->_productIds[$productId][$websiteId] = true;
            }
        }
    }
}
