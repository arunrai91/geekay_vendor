<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Block\Adminhtml\System\Config;

use Amasty\Sorting\Model\ConfigProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class YotpoReview extends Field
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    public function render(AbstractElement $element)
    {
        if (!$this->configProvider->isYotpoModuleEnabled()) {
            return '';
        }

        return parent::render($element);
    }
}
