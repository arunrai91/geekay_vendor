<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Controller\Adminhtml\ActionsLog;

use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractActionsLog;
use Amasty\AdminActionsLog\Model\LogEntry\Frontend\LogDetailsFormatter;
use Amasty\Base\Model\Serializer;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Preview extends AbstractActionsLog
{
    /**
     * @var LogDetailsFormatter
     */
    private $detailsFormatter;

    /**
     * @var Serializer
     */
    private $jsonSerializer;

    public function __construct(
        Context $context,
        LogDetailsFormatter $detailsFormatter,
        Serializer $jsonSerializer
    ) {
        parent::__construct($context);
        $this->detailsFormatter = $detailsFormatter;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if ($logEntryId = (int)$this->getRequest()->getParam('element_id')) {
            try {
                $data = $this->detailsFormatter->format($logEntryId);
                $result = [
                    'isError' => false,
                    'data' => $data
                ];
            } catch (\RuntimeException $e) {
                $result = [
                    'isError' => true,
                    'message' => $e->getMessage()
                ];
            } finally {
                $resultJson->setData($result);
            }
        }

        return $resultJson;
    }
}
