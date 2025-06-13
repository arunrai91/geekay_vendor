<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\DataProvider\LoginAttempt;

use Amasty\AdminActionsLog\Model\LoginAttempt\ResourceModel\Grid\CollectionFactory;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Listing extends AbstractDataProvider
{
    /**
     * @var SearchCriteria
     */
    private $searchCriteria;

    public function __construct(
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->searchCriteria = $searchCriteriaBuilder->create()->setRequestName($name);
        $this->collection->setSearchCriteria($this->searchCriteria);
    }

    /**
     * @return SearchCriteria
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }
}
