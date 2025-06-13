<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Block\Adminhtml\System\Config;

use Magento\Framework\Module\Manager;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;

class SuggestNotification extends Template
{
    /**
     * @var string
     */
    private $suggestLink = 'https://amasty.com/docs/doku.php?id=magento_2:product_feed&utm_source=extension'
    . '&utm_medium=backend&utm_campaign=suggest_pfeed#additional_packages_provided_in_composer_suggestions';

    /**
     * @var string
     */
    protected $_template = 'Amasty_Feed::config/information/suggest_notification.phtml';

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var string[]
     */
    private $suggestModules;

    public function __construct(
        Template\Context $context,
        Manager $moduleManager,
        array $data = [],
        array $suggestModules = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->suggestModules = $suggestModules;
        parent::__construct($context, $data);
    }

    public function getNotificationText(): Phrase
    {
        return __(
            'Extra features may be provided by additional packages in the extension\'s \'suggest\' section. '.
            'Please explore the available suggested packages'
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
