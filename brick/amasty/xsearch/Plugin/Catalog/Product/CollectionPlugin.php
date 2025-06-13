<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\Catalog\Product;

use Amasty\Shopby\Model\ResourceModel\Fulltext\Collection as ShopbyCollection;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as CatalogSearchCollection;

class CollectionPlugin
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Xsearch\Model\ResourceModel\StockSorting
     */
    private $sortingResource;

    /**
     * @var array
     */
    private $searchModules = [
        'catalogsearch',
        'amasty_xsearch'
    ];

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Xsearch\Model\ResourceModel\StockSorting $sortingResource
    ) {
        $this->request = $request;
        $this->sortingResource = $sortingResource;
    }

    /**
     * @param CatalogSearchCollection|ShopbyCollection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad($subject, $printQuery = false, $logQuery = false): array
    {
        if (in_array($this->request->getModuleName(), $this->searchModules)) {
            $this->sortingResource->addOutOfStockSortingToCollection($subject);
        }

        return [$printQuery, $logQuery];
    }
}
