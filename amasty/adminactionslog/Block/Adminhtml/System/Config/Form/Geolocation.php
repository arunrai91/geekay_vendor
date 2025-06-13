<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Block\Adminhtml\System\Config\Form;

use Amasty\Geoip\Helper\Data as GeoipHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager as ModuleManager;

class Geolocation extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var GeoIpHelper
     */
    private $geoipHelper;

    public function __construct(
        Context $context,
        ModuleManager $moduleManager,
        GeoipHelper $geoipHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->geoipHelper = $geoipHelper;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled(true);

        if ($this->moduleManager->isEnabled('Amasty_Geoip')
            && $this->geoipHelper->isDone(false)
        ) {
            $element->setDisabled(false);
        }

        return parent::_getElementHtml($element);
    }
}
