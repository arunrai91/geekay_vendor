<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\CatalogSearch\Block\Result;

use Magento\CatalogSearch\Block\Result;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Phrase;

class ChangeNoResultText
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    public function __construct(
        QueryFactory $queryFactory
    ) {
        $this->queryFactory = $queryFactory;
    }

    /**
     * @see Result::getNoResultText()
     *
     * @return Phrase
     */
    public function afterGetNoResultText(): Phrase
    {
        return __('We could not find anything for %1', $this->getQueryText());
    }

    /**
     * Extension point for change query text.
     *
     * @see \Amasty\ElasticSearch\Plugin\Xsearch\Plugin\CatalogSearch\Block\Result\ChangeNoResultText\ReplaceQueryText
     */
    public function getQueryText(): string
    {
        return $this->queryFactory->get()->getQueryText();
    }
}
