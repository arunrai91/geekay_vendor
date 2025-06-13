<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Helper;

use Amasty\ShopbyBase\Helper\FilterSetting as BaseFilterSetting;
use Amasty\ShopbyBase\Model\FilterSetting\FilterResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\ScopeInterface;

class FilterSetting extends BaseFilterSetting
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        FilterResolver $filterResolver,
        ScopeConfigInterface $scopeConfig,
        BlockFactory $blockFactory
    ) {
        parent::__construct($filterResolver, $scopeConfig);
        $this->blockFactory = $blockFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getShowMoreButtonBlock($setting)
    {
        return $this->blockFactory->createBlock(\Amasty\Shopby\Block\Navigation\Widget\HideMoreOptions::class)
            ->setFilterSetting($setting);
    }

    /**
     * @param string $path
     * @return bool
     * @deprecared
     */
    public function isSetConfig($path)
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
