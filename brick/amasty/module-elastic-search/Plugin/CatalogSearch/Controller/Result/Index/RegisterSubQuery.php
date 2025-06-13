<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Plugin\CatalogSearch\Controller\Result\Index;

use Amasty\ElasticSearch\Model\Search\SubQuery\Helper as SubQueryHelper;
use Magento\CatalogSearch\Controller\Result\Index as CatalogSearchController;
use Magento\Framework\App\RequestInterface;

class RegisterSubQuery
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SubQueryHelper
     */
    private $subQueryHelper;

    public function __construct(RequestInterface $request, SubQueryHelper $subQueryHelper)
    {
        $this->request = $request;
        $this->subQueryHelper = $subQueryHelper;
    }

    /**
     * Register sub query from request.
     *
     * @see CatalogSearchController::execute
     */
    public function beforeExecute(): void
    {
        $subQueryText = $this->request->getParam($this->subQueryHelper->getQueryParamName());
        if ($subQueryText !== null) {
            $this->subQueryHelper->setQueryText($subQueryText);
        }
    }
}
