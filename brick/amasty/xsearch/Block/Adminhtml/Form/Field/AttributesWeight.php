<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class AttributesWeight extends AbstractFieldArray
{
    /**
     * @var Attributes
     */
    protected $attributeRenderer = null;

    /**
     * @var Weight
     */
    protected $weightRenderer = null;

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'attributes_weight',
            [
                'label' => __('Attribute'),
                'renderer' => $this->getAttributeRenderer(),
            ]
        );

        $this->addColumn(
            'weight',
            [
                'label' => __('Weight'),
                'renderer' => $this->getWeightRenderer()
            ]
        );

        $this->_addAfter = false;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeRenderer()
    {
        if (!$this->attributeRenderer) {
            $this->attributeRenderer = $this->getLayout()->createBlock(
                Attributes::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->attributeRenderer;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getWeightRenderer()
    {
        if (!$this->weightRenderer) {
            $this->weightRenderer = $this->getLayout()->createBlock(
                Weight::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->weightRenderer;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options['option_' . $this->getAttributeRenderer()->calcOptionHash($row->getAttributes())]
            = 'selected="selected"';

        $options['option_' . $this->getWeightRenderer()->calcOptionHash($row->getWeight())]
            = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }
}
