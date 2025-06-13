<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Search\SubQuery;

use Magento\Framework\Stdlib\StringUtils;

class Helper
{
    public const QUERY_VAR_NAME = 'sub_query';

    /**
     * @var string|null
     */
    private $queryText;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    public function __construct(StringUtils $stringUtils)
    {
        $this->stringUtils = $stringUtils;
    }

    public function setQueryText(string $queryText): void
    {
        $this->queryText = $this->stringUtils->cleanString($queryText);
    }

    public function getQueryText(): ?string
    {
        return $this->queryText;
    }

    public function getQueryParamName(): string
    {
        return static::QUERY_VAR_NAME;
    }
}
