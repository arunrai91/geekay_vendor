<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */
namespace Amasty\Shopby\Block\Navigation;

interface RendererInterface
{
    public function collectFilters();

    public function getFilter();
}
