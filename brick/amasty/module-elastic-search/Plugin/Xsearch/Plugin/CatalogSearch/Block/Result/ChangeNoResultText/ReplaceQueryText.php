<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Plugin\Xsearch\Plugin\CatalogSearch\Block\Result\ChangeNoResultText;

use Amasty\ElasticSearch\Model\Search\SubQuery\Helper as SubQueryHelper;
use Amasty\Xsearch\Plugin\CatalogSearch\Block\Result\ChangeNoResultText;

class ReplaceQueryText
{
    /**
     * @var SubQueryHelper
     */
    private $subQueryHelper;

    public function __construct(SubQueryHelper $subQueryHelper)
    {
        $this->subQueryHelper = $subQueryHelper;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetQueryText(ChangeNoResultText $subject, callable $proceed): string
    {
        if ($subQueryText = $this->subQueryHelper->getQueryText()) {
            return $subQueryText;
        }

        return $proceed();
    }
}
