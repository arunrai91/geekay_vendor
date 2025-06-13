<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config\Form\Fieldset;

use Amasty\Base\Utils\XssStringEscaper;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Helper\Js;

class PromoFieldset extends Fieldset
{
    public const DEFAULT_PROMO_CONFIG = [
        'planType' => 'subscribe',
        'infoMessage' => ''
    ];

    public const PLAN_TYPES = [
        'subscribe' => [
            'iconSrc' => 'Amasty_Base::images/components/promotion-fieldset/subscribe.svg',
            'iconBgColor' => '#ebe7ff',
            'text' => 'Subscribe to Unlock',
        ],
        'upgrade' => [
            'iconSrc' => 'Amasty_Base::images/components/promotion-fieldset/upgrade.svg',
            'iconBgColor' => 'rgba(0, 133, 255, .1)',
            'text' => 'Upgrade Your Plan',
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

    /**
     * @var XssStringEscaper
     */
    private $xssEscaper;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Manager $moduleManager,
        Escaper $escaper,
        AssetRepository $assetRepository,
        string $moduleName,
        array $promoConfig = [],
        array $data = [],
        XssStringEscaper $xssEscaper = null // TODO move to not optional
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);

        $this->moduleName = $moduleName;
        $this->moduleManager = $moduleManager;
        $this->escaper = $escaper;
        $this->assetRepository = $assetRepository;
        $this->promoConfig = array_merge(static::DEFAULT_PROMO_CONFIG, $promoConfig);
        $this->xssEscaper = $xssEscaper  ?? ObjectManager::getInstance()->get(XssStringEscaper::class);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getChildrenElementsHtml(AbstractElement $element): string
    {
        if ($this->isNeedPromo()) {
            foreach ($element->getElements() as $field) {
                $field->setData('disabled', true);
            }
        }

        return parent::_getChildrenElementsHtml($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element): string
    {
        if ($this->isNeedPromo()) {
            $element->setLegend($this->getIcon() . $element->getLegend() . $this->getPromoText());
        }

        return parent::_getHeaderTitleHtml($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element): string
    {
        $originalComment = parent::_getHeaderCommentHtml($element);

        if ($this->isNeedPromo() && !empty($this->promoConfig['infoMessage'])) {
            $originalComment .= $this->getInfoMessage();
        }

        return $originalComment;
    }

    protected function isNeedPromo(): bool
    {
        return !$this->moduleManager->isEnabled($this->moduleName);
    }

    protected function getInfoMessage(): string
    {
        return <<<TEXT
                <div class="message message-info">
                    {$this->xssEscaper->escapeScriptInHtml($this->promoConfig['infoMessage'])}
                </div>
            TEXT;
    }

    protected function getIcon(): string
    {
        return <<<ICON
            <span class="ampromo-config-icon fieldset-icon"
                style="
                    background-color: {$this->getIconBackgroundColor()};
                    background-image: url('{$this->getIconUrl()}');
                "></span>
        ICON;
    }

    protected function getPromoText(): string
    {
        return <<<TEXT
            <span class="ampromo-config-fieldset-notification-message">
                {$this->getAdditionalText()}
            </span>
        TEXT;
    }

    protected function getIconBackgroundColor(): string
    {
        return self::PLAN_TYPES[$this->promoConfig['planType']]['iconBgColor'] ?? '';
    }

    protected function getIconUrl(): string
    {
        return $this->escaper->escapeUrl(
            $this->assetRepository->getUrl(
                self::PLAN_TYPES[$this->promoConfig['planType']]['iconSrc'] ?? ''
            )
        );
    }

    protected function getAdditionalText(): string
    {
        return self::PLAN_TYPES[$this->promoConfig['planType']]['text'] ?? '';
    }
}
