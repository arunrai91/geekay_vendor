<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Block\Adminhtml\Widget;

use Amasty\ShopbyBrand\Block\Data\Form\Element\MultiselectWithDisabledOptions;
use Amasty\ShopbyBrand\Model\Source\BrandsDisplay as BrandsDisplaySourceModel;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;

class BradsDisplay extends Template
{
    /**
     * @var ElementFactory
     */
    private $elementFactory;

    /**
     * @var BrandsDisplaySourceModel
     */
    private $brandsDisplaySourceModel;

    public function __construct(
        ElementFactory $elementFactory,
        BrandsDisplaySourceModel $brandsDisplaySourceModel,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->elementFactory = $elementFactory;
        $this->brandsDisplaySourceModel = $brandsDisplaySourceModel;
    }

    /**
     * @param AbstractElement $element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        /** @var MultiselectWithDisabledOptions $input */
        $input = $this->elementFactory->create(
            MultiselectWithDisabledOptions::class,
            ['data' => $element->getData()]
        );

        $input->setName($element->getName());
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setCanBeEmpty(true);
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $input->setValues($this->brandsDisplaySourceModel->toOptionArray());
        $input->setSize(count($input->getValues()));

        // restore value from moved setting `display_zero` to new `brands_display`
        $widgetValues = $element->getForm()->getParent()->getWidgetValues();
        if (!isset($widgetValues['brands_display'])) {
            if (!isset($widgetValues['display_zero']) || $widgetValues['display_zero'] == 1) {
                $input->setValue([BrandsDisplaySourceModel::DISPLAY_ZERO]);
            }
        }

        $html = $input->getElementHtml() . $this->renderTooltip();

        foreach ($input->getValues() as $value) {
            if ($value['value'] === BrandsDisplaySourceModel::DISPLAY_RELATED_TO_CATEGORY
                && empty($value['disabled'])
            ) {
                $element->setNote(null);
                break;
            }
        }

        $element->setData('after_element_html', $html);
        $element->setValue('');

        return $element;
    }

    private function renderTooltip(): string
    {
        return <<<TOOLTIP
            <div class="tooltip">
                <span class="help"><span></span></span>
                <div class="tooltip-content">
                    {$this->getTooltipContent()}
                </div>
            </div>
        TOOLTIP;
    }

    public function getTooltipContent(): string
    {
        return implode('', [
            '<p><b>Show Brands without Products</b> - disable to display only the brands '
            . 'that have products assigned to them.</p>',
            '<p><b>Show Brands Related to Category</b> - this option regulates display only on category pages, '
            . 'showing only the brands that have products in this category.</p>'
        ]);
    }
}
