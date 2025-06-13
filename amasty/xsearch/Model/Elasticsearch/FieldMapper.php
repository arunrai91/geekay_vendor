<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Elasticsearch;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

class FieldMapper implements FieldMapperInterface
{
    public function getFieldName($attributeCode, $context = [])
    {
        return $attributeCode;
    }

    public function getAllAttributesTypes($context = [])
    {
        return [];
    }
}
