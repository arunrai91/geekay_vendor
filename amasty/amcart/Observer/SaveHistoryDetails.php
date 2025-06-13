<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Observer;

use Amasty\Acart\Api\Data\HistoryInterface;
use Amasty\Acart\Model\History\ProductDetails\DetailSaver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveHistoryDetails implements ObserverInterface
{
    /**
     * @var DetailSaver
     */
    private $detailSaver;

    public function __construct(
        DetailSaver $detailSaver
    ) {
        $this->detailSaver = $detailSaver;
    }

    public function execute(Observer $observer)
    {
        /** @var HistoryInterface $history */
        $history = $observer->getEvent()->getHistory();
        if ($history && $history->getHistoryDetails()) {
            foreach ($history->getHistoryDetails() as $detail) {
                $detail->setHistoryId($history->getHistoryId());
                $this->detailSaver->execute($detail);
            }
        }
    }
}
