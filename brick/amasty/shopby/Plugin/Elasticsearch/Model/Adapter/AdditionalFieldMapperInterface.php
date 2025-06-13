<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter;

interface AdditionalFieldMapperInterface
{
    /**
     * @return array
     */
    public function getAdditionalAttributeTypes();

    /**
     * @param array $context
     * @return string
     */
    public function getFiledName($context);
}
