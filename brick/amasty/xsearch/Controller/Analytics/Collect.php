<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Controller\Analytics;

use Amasty\Xsearch\Model\Analytics\AnalyticsDataCollectionProcessor;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory as EmptyResponseFactory;
use Magento\Framework\Controller\ResultInterface;

class Collect implements HttpGetActionInterface
{
    public const TELEMETRY_PARAMETER = 'telemetry';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var AnalyticsDataCollectionProcessor
     */
    private $analyticsDataCollectionProcessor;

    /**
     * @var EmptyResponseFactory
     */
    private $resultFactory;

    public function __construct(
        RequestInterface $request,
        AnalyticsDataCollectionProcessor $analyticsDataCollectionProcessor,
        EmptyResponseFactory $resultFactory
    ) {
        $this->request = $request;
        $this->analyticsDataCollectionProcessor = $analyticsDataCollectionProcessor;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Collect analytics data
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute(): ResultInterface
    {
        if (!$this->isRequestValid()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
        } else {
            $telemetry = $this->request->getParam(self::TELEMETRY_PARAMETER, []);

            if (!empty($telemetry)) {
                $this->analyticsDataCollectionProcessor->process($telemetry);
            }
        }

        return $this->getResponse();
    }

    private function getResponse(): ResultInterface
    {
        return $this->resultFactory->create(EmptyResponseFactory::TYPE_JSON)->setData([]);
    }

    private function isRequestValid(): bool
    {
        return $this->request->isAjax();
    }
}
