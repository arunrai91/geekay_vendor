<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Controller\Adminhtml\VisitHistory;

use Amasty\AdminActionsLog\Api\VisitHistoryEntryRepositoryInterface;
use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractVisitHistory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class GetDetails extends AbstractVisitHistory
{
    /**
     * @var VisitHistoryEntryRepositoryInterface
     */
    private $visitHistoryEntryRepository;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    public function __construct(
        Context $context,
        VisitHistoryEntryRepositoryInterface $visitHistoryEntryRepository,
        TimezoneInterface $localeDate
    ) {
        parent::__construct($context);
        $this->visitHistoryEntryRepository = $visitHistoryEntryRepository;
        $this->localeDate = $localeDate;
    }

    public function execute()
    {
        $result = [];
        if ($id = (int)$this->getRequest()->getParam('id')) {
            $visitHistoryEntry = $this->visitHistoryEntryRepository->getById($id);
            $details = [];

            foreach ($visitHistoryEntry->getVisitHistoryDetails() as $detail) {
                $details[] = $detail->getData();
            }

            $sessionStart = $this->localeDate->formatDateTime(
                $visitHistoryEntry->getSessionStart() ?? '',
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::MEDIUM
            );
            $sessionEnd = $this->localeDate->formatDateTime(
                $visitHistoryEntry->getSessionEnd() ?? '',
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::MEDIUM
            );
            $result = $visitHistoryEntry->setVisitHistoryDetails($details)
                ->setSessionStart($sessionStart)
                ->setSessionEnd($sessionEnd)
                ->getData();
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
