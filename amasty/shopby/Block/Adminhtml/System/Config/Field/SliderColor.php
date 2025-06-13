<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Adminhtml\System\Config\Field;

use Amasty\Shopby\Block\Adminhtml\System\Config\Field\Renderer\SliderColor as SliderColorRenderer;
use Magento\Config\Block\System\Config\Form\Field;

class SliderColor extends Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $result = false;
        $renderer = $this->getLayout()->createBlock(SliderColorRenderer::class);

        if ($renderer) {
            $result = $renderer->toHtml();
        }

        return $result;
    }
}
