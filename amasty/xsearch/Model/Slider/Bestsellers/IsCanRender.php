<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Slider\Bestsellers;

use Amasty\Xsearch\Block\Adminhtml\System\Config\Form\Field\Bestsellers;
use Amasty\Xsearch\Model\Config;
use Amasty\Xsearch\Model\Slider\IsCanRenderInterface;
use Magento\Framework\Module\Manager;

class IsCanRender implements IsCanRenderInterface
{
    /**
     * @var ProductsProvider
     */
    private $productsProvider;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        ProductsProvider $productsProvider,
        Config $config,
        Manager $moduleManager
    ) {
        $this->productsProvider = $productsProvider;
        $this->config = $config;
        $this->moduleManager = $moduleManager;
    }

    public function execute(): bool
    {
        return $this->config->isBestsellersBlockEnabled()
            && $this->moduleManager->isEnabled(Bestsellers::MODULE_NAME)
            && count($this->productsProvider->getProducts());
    }
}
