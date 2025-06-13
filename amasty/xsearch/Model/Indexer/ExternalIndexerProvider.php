<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Indexer;

class ExternalIndexerProvider
{
    /**
     * @var array
     */
    private $sources;

    public function __construct(
        array $sources = []
    ) {
        $this->sources = $sources;
    }

    public function getDocuments(int $storeId)
    {
        foreach ($this->sources as $source) {
            foreach ($source->get($storeId) as $document) {
                yield $document;
            }
        }
    }
}
