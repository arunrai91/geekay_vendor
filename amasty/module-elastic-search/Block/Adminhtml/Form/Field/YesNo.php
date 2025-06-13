<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class YesNo extends Select
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    private $source;

    public function __construct(
        Context $context,
        \Magento\Config\Model\Config\Source\Yesno $source,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->source = $source;
    }

    /**
     * @param $value
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->source->toOptionArray());
        }

        if (!$this->_beforeToHtml()) {
            return '';
        }

        $html = '<div name="' . $this->getName() . '" class="admin__actions-switch ' . $this->getClass() . '">';
        $selectedHtml = ' <%= option_extra_attrs.option_' . self::calcOptionHash('1') . ' %>';
        $html .= ' <input id="' . $this->getName() . '" type="checkbox" class="admin__actions-switch-checkbox input-'
            . $this->getColumnName() . '" ' . $selectedHtml . '" name="' . $this->getName() . '"/>' .
            '<label class="admin__actions-switch-label ' . $this->getColumnName()  . '" for="' . $this->getName()
            . '" style="width: 70px;">' .
            '<span class="admin__actions-switch-text" data-text-on="'. __('Yes').  '" data-text-off="' . __('No')
            . '"></span></label>';
        $html .= '</div>';

        return $html;
    }
}
