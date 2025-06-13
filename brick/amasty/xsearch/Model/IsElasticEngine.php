<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model;

use Magento\Search\Model\EngineResolver;

class IsElasticEngine
{
    /**
     * @var EngineResolver
     */
    private $engineResolver;

    public function __construct(EngineResolver $engineResolver)
    {
        $this->engineResolver = $engineResolver;
    }

    public function execute(): bool
    {
        $engine = $this->getEngine();
        return strpos($engine, 'elastic') !== false || $engine === 'opensearch';
    }

    private function getEngine(): string
    {
        return $this->engineResolver->getCurrentSearchEngine();
    }
}
