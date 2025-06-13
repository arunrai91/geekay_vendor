<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Controller\Autocomplete;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\DesignLoader;

class Indexrecent implements HttpGetActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DesignLoader
     */
    private $designLoader;

    /**
     * @var ResponseInterface|Response
     */
    private $response;

    public function __construct(
        ResultFactory $resultFactory,
        RequestInterface $request,
        DesignLoader $designLoader,
        ResponseInterface $response
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->designLoader = $designLoader;
        $this->response = $response;
    }

    public function execute(): ResponseInterface
    {
        if (!$this->request->isAjax()) {
            $this->response->setStatusHeader(403, '1.1', 'Forbidden');
        } else {
            $this->designLoader->load();
            $layoutResult = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            $this->response->setBody($layoutResult->getLayout()->getOutput());
        }

        return $this->response;
    }
}
