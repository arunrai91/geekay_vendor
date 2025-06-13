<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\Elasticsearch5\Model\Adapter\FieldMapper;

use Amasty\Xsearch\Model\Elasticsearch\AdditionalFieldMapper;

class ProductFieldMapperProxyPlugin
{
    /**
     * @var AdditionalFieldMapper
     */
    private $additionalFieldMapper;

    public function __construct(AdditionalFieldMapper $additionalFieldMapper)
    {
        $this->additionalFieldMapper = $additionalFieldMapper;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllAttributesTypes($subject, array $result): array
    {
        return array_merge($result, $this->additionalFieldMapper->buildEntityFields());
    }
}
