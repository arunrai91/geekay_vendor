<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Promotions Manager for Magento 2
 */

namespace Amasty\Rgrid\Plugin\SalesRule\Model\Converter\ToModel;

use Magento\SalesRule\Model\Converter\ToModel;
use Magento\SalesRule\Model\Data\Condition;

class AttributeScope
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDataModelToArray(
        ToModel $subject,
        array $result,
        Condition $condition,
        $key = 'conditions'
    ): array {
        $extensions = $condition->getExtensionAttributes();
        if ($extensions && null !== $extensions->getAttributeScope()) {
            $result['attribute_scope'] = $extensions->getAttributeScope();
        }

        return $result;
    }
}
