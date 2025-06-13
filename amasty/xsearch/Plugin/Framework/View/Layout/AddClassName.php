<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\Framework\View\Layout;

use Amasty\Xsearch\Model\Config;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\Element;

/**
 * add class name with plugin - because ifconfig doesn't work with container
 */
class AddClassName
{
    public const CONTAINER_NAME = 'header.container';
    public const CLASS_NAME = 'amsearch-full-width';

    /**
     * @var Config
     */
    private $moduleConfigProvider;

    public function __construct(
        Config $moduleConfigProvider
    ) {
        $this->moduleConfigProvider = $moduleConfigProvider;
    }

    public function afterRenderNonCachedElement(Layout $subject, $html, $name)
    {
        if ($html && $name == self::CONTAINER_NAME && $this->moduleConfigProvider->isFullScreenEnabled()) {
            $class = $subject->getElementProperty($name, Element::CONTAINER_OPT_HTML_CLASS);
            $html = str_replace($class, $class . ' ' . self::CLASS_NAME, $html);
        }

        return $html;
    }
}
