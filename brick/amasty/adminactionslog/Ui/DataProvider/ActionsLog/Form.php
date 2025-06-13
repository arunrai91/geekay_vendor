<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\DataProvider\ActionsLog;

use Amasty\AdminActionsLog\Model\LogEntry\Frontend\LogDetailsFormatter;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\Grid\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Form extends AbstractDataProvider
{
    /**
     * @var LogDetailsFormatter
     */
    private $logDetailsFormatter;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        LogDetailsFormatter $logDetailsFormatter,
        TimezoneInterface $localeDate,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->logDetailsFormatter = $logDetailsFormatter;
        $this->localeDate = $localeDate;
    }

    public function getData()
    {
        $data = parent::getData();

        if ($data['totalRecords'] > 0) {
            $data['items'][0]['date'] = $this->localeDate->formatDateTime(
                $data['items'][0]['date'],
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::MEDIUM
            );
            $logId = (int)$data['items'][0]['id'];
            $data[$logId]['log_entry'] = $data['items'][0];
            try {
                $data[$logId]['log_entry'][LogEntry::LOG_DETAILS] = array_values(
                    $this->logDetailsFormatter->format($logId)
                );
            } catch (\RuntimeException $e) {
                $data[$logId]['log_entry']['messages'] = [
                    'isError' => true,
                    'message' => $e->getMessage()
                ];
                $data[$logId]['log_entry'][LogEntry::LOG_DETAILS] = [];
            }
        }

        return $data;
    }
}
