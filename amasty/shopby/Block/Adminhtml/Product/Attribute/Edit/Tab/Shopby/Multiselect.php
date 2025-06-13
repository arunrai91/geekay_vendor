<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Adminhtml\Product\Attribute\Edit\Tab\Shopby;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Multiselect extends Element implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'form/renderer/fieldset/multiselect.phtml';
}
