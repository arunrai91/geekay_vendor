<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry\Detail;

use Amasty\AdminActionsLog\Model\VisitHistoryEntry\DetailLoaderInterface;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryDetailCollectionFactory;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryDetail;

class FullDetailLoader implements DetailLoaderInterface
{
    /**
     * @var VisitHistoryDetailCollectionFactory
     */
    private $detailCollectionFactory;

    public function __construct(VisitHistoryDetailCollectionFactory $detailCollectionFactory)
    {
        $this->detailCollectionFactory = $detailCollectionFactory;
    }

    public function loadDetails(int $visitHistoryId): array
    {
        $collection = $this->detailCollectionFactory->create();
        $collection->addFieldToFilter(VisitHistoryDetail::VISIT_ID, $visitHistoryId);

        return $collection->getItems();
    }
}
