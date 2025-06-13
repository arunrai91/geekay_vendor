<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\ViewModel\CatalogSearch;

use Amasty\ElasticSearch\Model\Search\SubQuery\Helper as SubQueryHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface as UrlBuilder;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class FurtherQueryViewModel implements ArgumentInterface
{
    /**
     * @var SubQueryHelper
     */
    private $subQueryHelper;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        SubQueryHelper $subQueryHelper,
        UrlBuilder $urlBuilder,
        RequestInterface $request
    ) {
        $this->subQueryHelper = $subQueryHelper;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    public function getSubQueryParamName(): string
    {
        return $this->subQueryHelper->getQueryParamName();
    }

    public function getSubQueryText(): string
    {
        return $this->subQueryHelper->getQueryText() ?? '';
    }

    public function getSearchUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'catalogsearch/result',
            [
                '_secure' => $this->request->isSecure(),
                '_current' => true,
                '_use_rewrite' => true,
                '_query' => [
                    $this->getSubQueryParamName() => null,
                    'p' => null
                ]
            ]
        );
    }
}
