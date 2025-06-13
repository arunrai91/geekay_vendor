<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Block\Adminhtml\System\Config;

use Amasty\Base\Model\ModuleInfoProvider;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;

class Canonical extends Fieldset
{
    public const GUIDE_LINK = 'https://amasty.com/docs/doku.php?id=magento_2:improved_layered_navigation' .
    '&utm_source=extension&utm_medium=link&utm_campaign=iln_canonical_url_settings_m2';

    public const MARKET_GUIDE_LINK = 'https://marketplace.magento.com/amasty-shopby.html';

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        ModuleInfoProvider $moduleInfoProvider,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $link = $this->moduleInfoProvider->isOriginMarketplace() ? self::MARKET_GUIDE_LINK : self::GUIDE_LINK;

        $comment = '<p>';
        $comment .= $this->getCanonicalSettingComment();
        $comment .= '</p><p>';
        $comment .= __(
            'Need help with the setting?' .
            ' Please consult the <a target="_blank" href="%1">user guide</a> to configure properly.',
            $link
        );
        $comment .= '</p>';

        $element->setComment($comment);

        return parent::render($element);
    }

    private function getCanonicalSettingComment(): \Magento\Framework\Phrase
    {
        $routeParams = [
            'section' => 'catalog',
            'group' => 'seo',
            'field' => 'category_canonical_tag',
        ];

        if ($this->_urlBuilder->useSecretKey()) {
            $routeParams[UrlInterface::SECRET_KEY_PARAM_NAME] = $this->_urlBuilder->getSecretKey();
        }

        $settingsLink = $this->_urlBuilder->getUrl(
            'admin/system_config/edit',
            $routeParams
        );

        return __(
            'To get these settings working properly please make sure you have enabled the Canonical Meta Tag' .
            ' <a target="_blank" href="%1">here</a>' .
            ' (Stores > Configuration > Catalog > Search Engine Optimizations).',
            $settingsLink
        );
    }
}
