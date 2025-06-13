<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config\Form\Field\Promo;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Module\Manager;

class PromoGrid extends Template
{
    public const DEFAULT_PROMO_CONFIG = [
        'gridClass' => null,
        'gridImageSrc' => null,
        'buttonClass' => null,
        'buttonText' => null,
        'iconSrc' => null,
        'message' => null,
        'promoLink' => null,
        'promoType' => 'subscribe'
    ];

    public const DEFAULT_PROMO_ICONS = [
        'subscribe' => 'Amasty_Base::images/components/promotion-grid/lock.svg',
        'upgrade' => 'Amasty_Base::images/components/promotion-grid/lock-upgrade.svg'
    ];

    /**
     * @var string
     */
    protected $_template = 'Amasty_Base::config/promo-grid.phtml';

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

    public function __construct(
        Context $context,
        Manager $moduleManager,
        array $data = []
    ) {
        $this->moduleName = $data['moduleName'] ?? '';
        $this->moduleManager = $moduleManager;
        $this->promoConfig = array_merge(static::DEFAULT_PROMO_CONFIG, $data['promoConfig'] ?? []);
        if (!$this->promoConfig['iconSrc']) {
            $this->promoConfig['iconSrc'] = static::DEFAULT_PROMO_ICONS[$this->promoConfig['promoType']]
                ?? static::DEFAULT_PROMO_ICONS['subscribe'];
        }

        parent::__construct($context, $data);
    }

    public function toHtml(): string
    {
        if (!$this->moduleManager->isEnabled($this->moduleName)) {
            return parent::toHtml();
        }

        return '';
    }

    public function getPromoMessage(): string
    {
        return (string)$this->promoConfig['message'];
    }

    public function hasPromoMessage(): bool
    {
        return (bool)$this->promoConfig['message'];
    }

    public function getPromoGridClass(): string
    {
        return (string)$this->promoConfig['gridClass'];
    }

    public function getPromoButtonClass(): string
    {
        return (string)$this->promoConfig['buttonClass'];
    }

    public function getPromoButtonText(): string
    {
        return (string)$this->promoConfig['buttonText'] ?: __('Unlock functionality')->render();
    }

    public function getSubscribeUrl(): string
    {
        return (string)$this->promoConfig['promoLink'];
    }

    public function getGridImageSrc(): string
    {
        return $this->getViewFileUrl($this->promoConfig['gridImageSrc']);
    }

    public function getIconUrl(): string
    {
        return $this->getViewFileUrl($this->promoConfig['iconSrc']);
    }
}
