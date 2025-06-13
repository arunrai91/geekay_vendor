<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Message;

use Amasty\ElasticSearch\Model\Config;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Notification\MessageInterface;

class HardcodedEngine implements MessageInterface
{
    public const INITIAL_CONFIG_ENGINE_PATH = 'system/default/catalog/search/engine';

    /**
     * @var string
     */
    private $initialSearchEngineConfig;

    public function __construct(
        DeploymentConfig $initialConfigSource
    ) {
        $this->initialSearchEngineConfig = (string)$initialConfigSource->get(self::INITIAL_CONFIG_ENGINE_PATH);
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return hash('sha256', 'HARDCODED_SEARCHENGINE');
    }

    /**
     * @return bool
     */
    public function isDisplayed()
    {
        return $this->initialSearchEngineConfig
            && strpos($this->initialSearchEngineConfig, Config::ELASTIC_SEARCH_ENGINE) === false;
    }

    /**
     * @inheritdoc
     */
    public function getText()
    {
        return __(
            'Amasty Elastic is not working because "%1" search engine is set in app/etc/env.php file.',
            $this->initialSearchEngineConfig
        );
    }

    /**
     * @inheritdoc
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
}
