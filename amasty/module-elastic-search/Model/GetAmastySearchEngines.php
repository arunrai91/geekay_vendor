<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model;

class GetAmastySearchEngines
{
    /**
     * @var string[]
     */
    private $amastySearchEngines;

    public function __construct(array $amastySearchEngines = [])
    {
        $this->amastySearchEngines = array_values($amastySearchEngines);
    }

    /**
     * @return string[]
     */
    public function execute(): array
    {
        return $this->amastySearchEngines;
    }
}
