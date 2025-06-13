<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Promotions Manager for Magento 2
 */

namespace Amasty\Rgrid\Plugin\SalesRule\Model\Converter\ToDataModel;

use Magento\SalesRule\Model\Converter\ToDataModel;
use Magento\SalesRule\Model\Data\Condition;

class AttributeScope
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterArrayToConditionDataModel(ToDataModel $subject, Condition $result, array $input): Condition
    {
        if (isset($input['attribute_scope'])) {
            $extensions = $result->getExtensionAttributes();
            if (null !== $extensions->getAttributeScope()) {
                return $result;
            }
            $extensions->setAttributeScope($input['attribute_scope']);
            $result->setExtensionAttributes($extensions);
        }

        return $result;
    }
}
