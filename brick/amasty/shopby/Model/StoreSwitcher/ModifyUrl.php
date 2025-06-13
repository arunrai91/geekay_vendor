<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\StoreSwitcher;

use Amasty\Shopby\Plugin\Store\ViewModel\SwitcherUrlProvider\ModifyUrlData;
use Amasty\ShopbyBase\Model\UrlBuilder\UrlModifier;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcherInterface;

class ModifyUrl implements StoreSwitcherInterface
{
    /**
     * @var UrlModifier
     */
    private $urlModifier;

    /**
     * @var CategoryRegistry
     */
    private $categoryRegistry;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(UrlModifier $urlModifier, CategoryRegistry $categoryRegistry, RequestInterface $request)
    {
        $this->urlModifier = $urlModifier;
        $this->categoryRegistry = $categoryRegistry;
        $this->request = $request;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $categoryId = $this->request->getParam(ModifyUrlData::CATEGORY_ID, $this->categoryRegistry->get());
        if ($categoryId === null) {
            return $redirectUrl;
        }

        return $this->urlModifier->execute($redirectUrl, (int) $categoryId, true);
    }
}
