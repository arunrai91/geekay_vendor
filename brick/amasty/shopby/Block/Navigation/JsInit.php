<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Model\Config\MobileConfigResolver;
use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\UrlResolver\UrlResolverInterface;
use Magento\Framework\View\Element\Template;

/**
 * @api
 */
class JsInit extends \Magento\Framework\View\Element\Template
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'jsinit.phtml';

    /**
     * @var UrlResolverInterface
     */
    private $urlResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var MobileConfigResolver
     */
    private $mobileConfigResolver;

    public function __construct(
        Template\Context $context,
        UrlResolverInterface $urlResolver,
        ConfigProvider $configProvider,
        MobileConfigResolver $mobileConfigResolver,
        array $data = []
    ) {
        $this->urlResolver = $urlResolver;
        $this->configProvider = $configProvider;
        $this->mobileConfigResolver = $mobileConfigResolver;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return int
     */
    public function collectFilters()
    {
        return $this->mobileConfigResolver->getSubmitFilterMode();
    }

    /**
     * @return string
     */
    public function getClearUrl(): string
    {
        return $this->urlResolver->resolve();
    }

    /**
     * @return bool
     */
    public function getEnableStickySidebarDesktop(): bool
    {
        return $this->configProvider->isEnableStickySidebarDesktop();
    }
}
