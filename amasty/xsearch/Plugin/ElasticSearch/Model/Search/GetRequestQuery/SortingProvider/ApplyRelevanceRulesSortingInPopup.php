<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\ElasticSearch\Model\Search\GetRequestQuery\SortingProvider;

use Amasty\ElasticSearch\Model\Search\GetRequestQuery;
use Amasty\Xsearch\Model\Config as ModuleConfig;
use Magento\Framework\App\RequestInterface as HttpRequest;

class ApplyRelevanceRulesSortingInPopup
{
    public const XSEARCH_MODULE_NAME = 'amasty_xsearch';

    /**
     * @var ModuleConfig
     */
    private $config;

    /**
     * @var HttpRequest
     */
    private $request;

    public function __construct(
        ModuleConfig $config,
        HttpRequest $request
    ) {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param GetRequestQuery $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsCanApplyRelevanceSorting(GetRequestQuery $subject, bool $result): bool
    {
        if ($this->isCanApply() && !$this->config->isApplyRelevanceRulesInPopup()) {
            $result = false;
        }

        return $result;
    }

    private function isCanApply(): bool
    {
        return $this->request->getModuleName() === self::XSEARCH_MODULE_NAME;
    }
}
