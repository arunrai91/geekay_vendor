<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Block\Adminhtml\Page\Edit\Tab\Selection;

use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class Option extends Widget implements RendererInterface
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'attribute/option.phtml';

    /**
     * @var AbstractAttribute|null
     */
    private ?AbstractAttribute $eavAttribute = null;

    /**
     * @var int|null
     */
    private ?int $attributeIdx = null;

    /**
     * @var  mixed
     */
    private $attributeValue;

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValueHtml()
    {
        $block = $this->getLayout()
            ->createBlock(\Amasty\ShopbyPage\Block\Adminhtml\Page\Edit\Tab\Selection\Value::class)
            ->setEavAttributeValue($this->attributeValue)
            ->setEavAttributeIdx($this->attributeIdx);

        if ($this->eavAttribute) {
            $block->setEavAttribute($this->eavAttribute);
        }

        return $block->toHtml();
    }

    /**
     * @param AbstractAttribute $attribute
     * @return $this
     */
    public function setEavAttribute(AbstractAttribute $attribute)
    {
        $this->eavAttribute = $attribute;
        return $this;
    }

    /**
     * @param $idx
     * @return $this
     */
    public function setEavAttributeIdx($idx)
    {
        $this->attributeIdx = (int)$idx;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setEavAttributeValue($value)
    {
        $this->attributeValue = $value;
        return $this;
    }
}
