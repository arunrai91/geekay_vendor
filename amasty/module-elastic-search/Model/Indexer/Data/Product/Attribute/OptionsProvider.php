<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Data\Product\Attribute;

use Magento\Eav\Model\Entity\Attribute;

class OptionsProvider
{
    /**
     * @var array [attrId => [storeId => [optValue => optLabel, ...], ...], ...]
     */
    private $options = [];

    public function execute(Attribute $attribute, int $storeId): array
    {
        if (!in_array($attribute->getFrontendInput(), ['select', 'multiselect', 'boolean'], true)) {
            return [];
        }

        if (!isset($this->options[$attribute->getAttributeId()][$storeId])) {
            $this->options[$attribute->getAttributeId()][$storeId] = [];
            $prevStoreId = $attribute->getStoreId();
            $attribute->setStoreId($storeId);

            foreach ($attribute->getOptions() as $option) {
                $this->options[$attribute->getAttributeId()][$storeId][$option->getValue()] = $option->getLabel();
            }

            $attribute->setStoreId($prevStoreId);
        }

        return $this->options[$attribute->getAttributeId()][$storeId];
    }
}
