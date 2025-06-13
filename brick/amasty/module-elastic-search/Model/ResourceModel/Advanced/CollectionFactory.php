<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\ResourceModel\Advanced;

use Amasty\Base\Model\MagentoVersion as MagentoVersion;
use Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection;

class CollectionFactory
{
    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        MagentoVersion $magentoVersion,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->magentoVersion = $magentoVersion;
        $this->objectManager = $objectManager;
    }

    /**
     * @return Collection
     */
    public function create()
    {
        if (version_compare($this->magentoVersion->get(), '2.3.2', '<')) {
            return $this->objectManager->create(Collection::class);
        } else {
            return $this->objectManager->create('elasticsearchAdvancedCollection');
        }
    }
}
