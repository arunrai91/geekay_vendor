<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\Widget\Form\Renderer;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Element extends Template implements RendererInterface
{
    /**
     * @var AbstractElement|null
     */
    private ?AbstractElement $element = null;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form/renderer/element.phtml';

    public function getElement(): ?AbstractElement
    {
        return $this->element;
    }

    /**
     * Render the element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->element = $element;
        return $this->toHtml();
    }
}
