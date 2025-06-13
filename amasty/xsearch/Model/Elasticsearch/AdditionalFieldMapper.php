<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Elasticsearch;

class AdditionalFieldMapper
{
    /**
     * @var array
     */
    private $fieldMappers;

    public function __construct(array $fieldMappers = [])
    {
        $this->fieldMappers = $fieldMappers;
    }

    public function buildEntityFields(): array
    {
        $additionalFields = [];
        foreach ($this->fieldMappers as $fieldMapper) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
            $additionalFields = array_merge($additionalFields, $fieldMapper->buildEntityFields());
        }

        return $additionalFields;
    }
}
