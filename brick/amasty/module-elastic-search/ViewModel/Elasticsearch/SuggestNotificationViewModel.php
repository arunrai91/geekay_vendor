<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\ViewModel\Elasticsearch;

use Magento\Framework\Module\Manager;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SuggestNotificationViewModel implements ArgumentInterface
{
    /**
     * @var string
     */
    private $suggestLink = 'https://amasty.com/docs/doku.php?id=magento_2:elastic_search&utm_source=extension'
    . '&utm_medium=backend&utm_campaign=suggest_elasticsearch#additional_packages_provided_in_composer_suggestions';

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var string[]
     */
    private $suggestModules;

    public function __construct(
        Manager $moduleManager,
        array $suggestModules = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->suggestModules = $suggestModules;
    }

    public function getNotificationText(): Phrase
    {
        return __(
            'Extra features are provided by additional packages in the extension\'s \'suggest\' section. '
            . 'Please explore the available suggested packages'
        );
    }

    public function getSuggestLink(): string
    {
        return $this->suggestLink;
    }

    public function shouldShowNotification(): bool
    {
        foreach ($this->suggestModules as $module) {
            if (!$this->moduleManager->isEnabled($module)) {
                return true;
            }
        }

        return false;
    }
}
