<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Module\Manager;

class PromoFormScript extends Template
{
    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Context $context,
        Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
    }

    protected function _toHtml(): string
    {
        if ($this->moduleManager->isEnabled('Amasty_Promo')) {
            return parent::_toHtml();
        }

        return '';
    }
}
