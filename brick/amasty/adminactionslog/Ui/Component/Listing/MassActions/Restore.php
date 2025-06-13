<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\Component\Listing\MassActions;

use Amasty\AdminActionsLog\Model\ConfigProvider;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;

class Restore extends Action
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ContextInterface $context,
        ConfigProvider $configProvider,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        parent::__construct($context, $components, $data, $actions);
        $this->configProvider = $configProvider;
    }

    public function getConfiguration()
    {
        $config =  parent::getConfiguration();
        $config['confirm']['message'] = $this->configProvider->getRestoreSettingsText()
            . ' ' . __('Are you sure you want to restore selected items?');

        return $config;
    }
}
