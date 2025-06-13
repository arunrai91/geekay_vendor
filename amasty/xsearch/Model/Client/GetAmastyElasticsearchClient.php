<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Client;

use Amasty\Base\Model\Di\Wrapper as DiWrapper;
use Amasty\ElasticSearch\Model\Client\ClientInterface;
use Amasty\ElasticSearch\Model\Client\ClientRepositoryInterface;
use Amasty\Xsearch\Model\Config;

class GetAmastyElasticsearchClient
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DiWrapper|ClientRepositoryInterface
     */
    private $amastyElasticsearchClientRepository;

    public function __construct(
        Config $config,
        DiWrapper $amastyElasticsearchClientRepository
    ) {
        $this->config = $config;
        $this->amastyElasticsearchClientRepository = $amastyElasticsearchClientRepository;
    }

    public function execute(): ?ClientInterface
    {
        if (!$this->config->isAmastyElasticEngine()) {
            return null;
        }

        return $this->amastyElasticsearchClientRepository->get();
    }
}
