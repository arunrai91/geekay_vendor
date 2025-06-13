<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\ViewModel\Elasticsearch;

use Amasty\ElasticSearch\Exception\NotAmastySearchEngine;
use Amasty\ElasticSearch\Model\Client\ClientRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SystemPackageViewModel implements ArgumentInterface
{
    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    public function __construct(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function isAvailable(): bool
    {
        try {
            return $this->clientRepository->get()->isSystemPackageAvailable();
        } catch (NotAmastySearchEngine $e) {
            return true;
        }
    }

    public function generateMessage(): string
    {
        $client = $this->clientRepository->get();
        return __('Elasticsearch package is not installed. Please, run the following
            command in the SSH: composer require %1 ^%2.
            You can check Magento system requirements
            <a target="_blank" href="https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements.html">
            here</a>', $client->getSystemPackageName(), $client->getNeededSystemPackageVersion())->render();
    }
}
