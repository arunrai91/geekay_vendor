<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\IpLog;

use Amasty\Geoip\Api\Data\IpLogInterface;
use Amasty\Geoip\Model\ResourceModel\IpLog\CollectionFactory as IpLogCollectionFactory;
use Amasty\Geoip\Model\ResourceModel\IpLog\Collection as IpLogCollection;

class UpdateBatcher
{
    /**
     * NOTE: Do not change batch size. Service will ignore the rest of records after that.
     */
    private const PAGE_SIZE = 25;

    /**
     * @var IpLogCollectionFactory
     */
    private $ipLogCollectionFactory;

    /**
     * @var null|IpLogCollection
     */
    private $ipLogCollection = null;

    /**
     * @var int
     */
    private $currentPage = 1;

    /**
     * @var int
     */
    private $pageAmount = 0;

    public function __construct(
        IpLogCollectionFactory $logIpCollectionFactory
    ) {
        $this->ipLogCollectionFactory = $logIpCollectionFactory;
    }

    /**
     * @return IpLogInterface[]|null
     */
    public function getBatch(): ?array
    {
        $this->initCollection();

        if ($this->currentPage > $this->pageAmount) {
            return null;
        }

        $this->ipLogCollection->setCurPage($this->currentPage);

        $items = $this->ipLogCollection->getItems();

        $this->ipLogCollection->clear();
        $this->currentPage++;

        return $items;
    }

    private function initCollection(): void
    {
        if (null !== $this->ipLogCollection) {
            return;
        }

        $this->ipLogCollection = $this->ipLogCollectionFactory->create();
        $this->ipLogCollection->addFieldToFilter(
            [IpLogInterface::LAST_SYNC, IpLogInterface::LAST_SYNC],
            [
                ['null' => true],
                ['lt' => date('Y-m-d', strtotime('-1 week'))]
            ]
        );
        $this->ipLogCollection->setOrder(IpLogInterface::LOG_ID, $this->ipLogCollection::SORT_ORDER_ASC);
        $this->ipLogCollection->setPageSize(self::PAGE_SIZE);
        $this->pageAmount = $this->ipLogCollection->getLastPageNumber();
    }
}
