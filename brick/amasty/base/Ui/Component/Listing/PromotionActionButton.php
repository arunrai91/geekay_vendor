<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Ui\Component\Listing;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class PromotionActionButton implements ButtonProviderInterface
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $cssClass;

    /**
     * @var string
     */
    private $promoUrl;

    /**
     * @var array
     */
    private $additionalConfig;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        Escaper $escaper,
        string $label,
        string $promoUrl,
        string $cssClass = 'subscribe-to-unlock',
        array $additionalConfig = []
    ) {
        $this->escaper = $escaper;
        $this->label = $label;
        $this->promoUrl = $promoUrl;
        $this->cssClass = $cssClass;
        $this->additionalConfig = $additionalConfig;
    }

    public function getButtonData(): array
    {
        return array_merge([
            'class' => $this->cssClass . ' amasty-promotion-action-button',
            'label' => $this->label,
            'on_click' => sprintf("window.open('%s', '_blank');", $this->escaper->escapeUrl($this->promoUrl)),
        ], $this->additionalConfig);
    }
}
