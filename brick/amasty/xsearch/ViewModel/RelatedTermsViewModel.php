<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\ViewModel;

use Amasty\Xsearch\Model\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Search\Model\QueryFactory;

class RelatedTermsViewModel implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    public function __construct(
        Config $config,
        QueryFactory $queryFactory
    ) {
        $this->config = $config;
        $this->queryFactory = $queryFactory;
    }

    public function isCanShow(): bool
    {
        return $this->config->canShowRelatedTerms($this->queryFactory->get()->getNumResults());
    }
}
