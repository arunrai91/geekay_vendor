<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Block\Adminhtml\Page\Edit\Tab\Selection;

use Amasty\ShopbyPage\Model\Request\Page\SelectionAttributeRegistry;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * @api
 */
class Value extends Widget
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'attribute/value.phtml';

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

    /**
     * @var SelectionAttributeRegistry
     */
    private SelectionAttributeRegistry $selectionAttributeRegistry;

    public function __construct(
        SelectionAttributeRegistry $selectionAttributeRegistry,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->selectionAttributeRegistry = $selectionAttributeRegistry;
    }

    /**
     * Get attribute
     * @return  AbstractAttribute
     */
    public function getEavAttribute()
    {
        if ($this->eavAttribute === null) {
            $this->eavAttribute = $this->selectionAttributeRegistry->getAttribute();
        }
        return $this->eavAttribute;
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
     * @return array
     */
    public function getAttributeOptions()
    {
        return $this->getEavAttribute()->getFrontend()->getSelectOptions();
    }

    /**
     * @return string
     */
    public function getInputName()
    {
        return 'conditions[' . $this->getEavAttributeIdx() . '][value]';
    }

    /**
     * @return int|mixed
     */
    public function getEavAttributeIdx()
    {
        if ($this->attributeIdx === null) {
            $this->attributeIdx = $this->selectionAttributeRegistry->getAttributeIdx();
        }
        return $this->attributeIdx;
    }

    /**
     * @param $idx
     * @return $this
     */
    public function setEavAttributeIdx($idx)
    {
        $this->attributeIdx = $idx;
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

    /**
     * @return mixed
     */
    public function getEavAttributeValue()
    {
        return $this->attributeValue;
    }

    /**
     * @return mixed|null|string
     */
    public function getFrontendInput()
    {
        return $this->getEavAttribute() ? $this->getEavAttribute()->getFrontendInput() : null;
    }
}
