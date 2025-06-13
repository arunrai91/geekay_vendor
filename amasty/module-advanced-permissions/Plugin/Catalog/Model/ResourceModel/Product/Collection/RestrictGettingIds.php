<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Catalog\Model\ResourceModel\Product\Collection;

use Amasty\Rolepermissions\Model\Restriction\Product\Collection\RestrictInterface as CollectionRestrictInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class RestrictGettingIds
{
    /**
     * @var CollectionRestrictInterface
     */
    private $productCollectionRestrict;

    public function __construct(
        CollectionRestrictInterface $productCollectionRestrict
    ) {
        $this->productCollectionRestrict = $productCollectionRestrict;
    }

    public function beforeGetAllIds(ProductCollection $subject): void
    {
        $this->productCollectionRestrict->execute($subject);
    }
}
