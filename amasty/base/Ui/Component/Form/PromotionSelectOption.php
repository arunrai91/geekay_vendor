<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Ui\Component\Form;

use Magento\Framework\Module\Manager;

class PromotionSelectOption
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $value;

    /**
     * @var PromotionSelectOption[]
     */
    private $optionGroup;

    /**
     * @var string|null
     */
    private $link;

    /**
     * @var array
     */
    private $additionalOptionParams;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Manager $moduleManager,
        string $moduleName = '',
        string $label = '',
        string $value = '',
        array $optionGroup = [],
        string $link = null,
        array $additionalOptionParams = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->moduleName = $moduleName;
        $this->label = $label;
        $this->value = $value;
        $this->optionGroup = $optionGroup;
        $this->link = $link;
        $this->additionalOptionParams = $additionalOptionParams;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function setModuleName(string $moduleName): void
    {
        $this->moduleName = $moduleName;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function addOptionGroup(PromotionSelectOption $option): void
    {
        $this->optionGroup[] = $option;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    public function setAdditionalOptionParams(array $additionalOptionParams): void
    {
        $this->additionalOptionParams = $additionalOptionParams;
    }

    public function toArray(): array
    {
        $option = [
            'label' => $this->label,
            'value' => $this->value,
            'isPromo' => !$this->moduleManager->isEnabled($this->moduleName),
            'promoLink' => $this->link
        ];

        if (!empty($this->optionGroup)) {
            $option['optgroup'] = array_map(function ($option) {
                return $option->toArray();
            }, $this->optionGroup);
        }

        return array_merge($option, $this->additionalOptionParams);
    }
}
