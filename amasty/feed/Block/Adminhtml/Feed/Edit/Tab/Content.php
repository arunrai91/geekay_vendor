<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Amasty\Feed\Model\OptionSource\Feed\Attribute as AttributeOptionSource;
use Amasty\Feed\Model\OptionSource\Feed\Format as FormatOptionSource;
use Amasty\Feed\Model\OptionSource\Feed\Modifier as ModifierOptionSource;
use Amasty\Feed\Model\OptionSource\Feed\ParentFlag as ParentFlagOptionSource;
use Amasty\Feed\Model\OptionSource\Feed\YesNo as YesNoOptionSource;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Content extends Widget implements RendererInterface
{
    /**
     * @var AttributeOptionSource
     */
    private $attributeOptionSource;

    /**
     * @var FormatOptionSource
     */
    private $formatOptionSource;

    /**
     * @var YesNoOptionSource
     */
    private $yesNoOptionSource;

    /**
     * @var ParentFlagOptionSource
     */
    private $parentFlagOptionSource;

    /**
     * @var ModifierOptionSource
     */
    private $modifierOptionSource;

    public function __construct(
        Context $context,
        AttributeOptionSource $attributeOptionSource,
        FormatOptionSource $formatOptionSource,
        YesNoOptionSource $yesNoOptionSource,
        ParentFlagOptionSource $parentFlagOptionSource,
        ModifierOptionSource $modifierOptionSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeOptionSource = $attributeOptionSource;
        $this->formatOptionSource = $formatOptionSource;
        $this->yesNoOptionSource = $yesNoOptionSource;
        $this->parentFlagOptionSource = $parentFlagOptionSource;
        $this->modifierOptionSource = $modifierOptionSource;
    }

    public function render(AbstractElement $element): string
    {
        $this->setElement($element);

        return $this->toHtml();
    }

    public function getFormatOptions(): array
    {
        return $this->formatOptionSource->toOptionArray();
    }

    public function getYesNoOptions(): array
    {
        return $this->yesNoOptionSource->toOptionArray();
    }

    public function getParentFlagOptions(): array
    {
        return $this->parentFlagOptionSource->toOptionArray();
    }

    public function getAttributeOptions(): array
    {
        return $this->attributeOptionSource->toOptionArray();
    }

    public function getModiftVars(): array
    {
        return $this->modifierOptionSource->toArray();
    }

    public function getArgs(): array
    {
        $args = [
            'replace' => [
                __('From'),
                __('To'),
            ],
            'prepend' => [
                __('Text'),
            ],
            'if_empty' => [
                __('Text'),
            ],
            'if_not_empty' => [
                __('Text'),
            ],
            'full_if_not_empty' => [
                __('Empty'),
                __('Not Empty'),
            ],
            'append' => [
                __('Text'),
            ],
            'length' => [
                __('Max Length'),
            ],
            'prepend_if_not_empty' => [
                __('Text'),
            ],
            'append_if_not_empty' => [
                __('Text'),
            ],
        ];

        foreach ($args as $index => $value) {
            $args[$this->escapeHtml($index)] = $this->escapeHtml($value);
        }

        return $args;
    }
}
