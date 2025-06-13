<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config\Form\Field\Promo;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class PromoField extends Field
{
    public const PROMO_CONFIGS = [
        'subscribe' => [
            'isIconVisible' => true,
            'iconBgColor' => '#ebe7ff',
            'iconSrc' => 'Amasty_Base::images/components/promotion-field/lock.svg',
            'subscribeText' => 'Subscribe to Unlock',
            'promoLink' => null
        ],
        'upgrade' => [
            'isIconVisible' => true,
            'iconBgColor' => 'rgba(0, 133, 255, .1)',
            'iconSrc' => 'Amasty_Base::images/components/promotion-field/lock-upgrade.svg',
            'subscribeText' => 'Upgrade Your Plan',
            'promoLink' => null
        ]
    ];

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string[]
     */
    private $promoConfig;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    public function __construct(
        Context $context,
        Manager $moduleManager,
        Escaper $escaper,
        AssetRepository $assetRepository,
        string $moduleName,
        array $promoConfig = [],
        array $data = [],
        string $currentPromoConfig = 'subscribe'
    ) {
        $this->moduleName = $moduleName;
        $this->moduleManager = $moduleManager;
        $this->escaper = $escaper;
        $this->assetRepository = $assetRepository;
        $this->promoConfig = array_merge(
            static::PROMO_CONFIGS[$currentPromoConfig] ?? static::PROMO_CONFIGS['subscribe'],
            $promoConfig
        );
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($this->moduleManager->isEnabled($this->moduleName)) {
            return parent::render($element);
        }

        $element->setDisabled(true);
        $element->setReadonly(true);
        if (isset($this->promoConfig['comment'])) {
            $element->setComment($this->promoConfig['comment']);
        }

        $html = $this->renderLabel($element);
        $html .= $this->_renderValue($element);
        $html .= $this->_renderHint($element);

        if ($element->getData('ext_type') === 'multiple') {
            $html = str_replace('<select', '<select readonly="1"', $html);
        }

        return $this->_decorateRowHtml($element, $html);
    }

    public function renderLabel(AbstractElement $element): string
    {
        return <<<LABEL
            <td class="label">
                <div class="ampromo-config-label">
                    {$this->getIcon()}
                    <div class="ampromo-config-content-container">
                        <div>
                            <label for="{$element->getHtmlId()}">
                                <span>
                                    {$element->getLabel()}
                                </span>
                            </label>
                        </div>
                        <div class="ampromo-config-notification-message">
                            {$this->escaper->escapeHtml(__($this->promoConfig['subscribeText']))}
                        </div>
                    </div>
                </div>
            </td>
        LABEL;
    }

    protected function getIcon(): string
    {
        if ($this->promoConfig['isIconVisible'] === false) {
            return '';
        }

        $icon = <<<ICON
            <span class="ampromo-config-icon"
                style="
                    background-color: {$this->promoConfig['iconBgColor']};
                    background-image: url('{$this->getIconUrl()}');
                "></span>
        ICON;

        if (!$this->promoConfig['promoLink']) {
            return $icon;
        }

        return <<<LINK
            <a href="{$this->promoConfig['promoLink']}" target="_blank">
                {$icon}
            </a>
        LINK;
    }

    protected function getIconUrl(): string
    {
        return $this->escaper->escapeUrl($this->assetRepository->getUrl($this->promoConfig['iconSrc']));
    }
}
