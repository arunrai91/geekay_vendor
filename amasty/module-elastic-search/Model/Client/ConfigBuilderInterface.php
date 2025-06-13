<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Client;

interface ConfigBuilderInterface
{
    /**
     * @param array $clientOptions
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(array $clientOptions): array;
}
