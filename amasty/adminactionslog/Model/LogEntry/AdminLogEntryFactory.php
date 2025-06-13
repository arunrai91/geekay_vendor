<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LogEntry;

use Amasty\AdminActionsLog\Model\Admin\SessionUserDataProvider;
use Magento\Framework\Stdlib\DateTime\DateTime;

class AdminLogEntryFactory
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var LogEntryFactory
     */
    private $logEntryFactory;

    /**
     * @var SessionUserDataProvider
     */
    private $userDataProvider;

    public function __construct(
        DateTime $dateTime,
        LogEntryFactory $logEntryFactory,
        SessionUserDataProvider $userDataProvider
    ) {
        $this->dateTime = $dateTime;
        $this->logEntryFactory = $logEntryFactory;
        $this->userDataProvider = $userDataProvider;
    }

    public function create(array $data = [])
    {
        $data = array_merge(
            [
                LogEntry::DATE => $this->dateTime->gmtDate(),
                LogEntry::USERNAME => $this->userDataProvider->getUserName(),
                LogEntry::PARAMETER_NAME => 'id',
                LogEntry::STORE_ID => 0,
                LogEntry::IP => $this->userDataProvider->getIpAddress(),
            ],
            $data
        );
        $logEntry = $this->logEntryFactory->create(['data' => $data]);
        $logEntry->setHasDataChanges(true);

        return $logEntry;
    }
}
