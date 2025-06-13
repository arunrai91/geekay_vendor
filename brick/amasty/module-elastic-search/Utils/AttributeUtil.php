<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Utils;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class AttributeUtil
{
    /**
     * Is attribute value storage system are complex
     *
     * Complex attribute values are IDs of options.
     * Sometimes we need finish value (options)
     */
    public function isComplexAttribute(Attribute $attribute): bool
    {
        if ($attribute->usesSource()) {
            return true;
        }

        $frontendInput = $attribute->getFrontendInput();

        return $frontendInput === 'select'
            || $frontendInput === 'multiselect';
    }
}
