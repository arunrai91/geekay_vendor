<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\ShopbySeo\Model\SeoOptions;

use Amasty\ShopbyBrand\Model\ConfigProvider;
use Amasty\ShopbySeo\Model\SeoOptions;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;

/**
 * Exclude brand filter from SEO on "all products" page
 *
 * Brand filter on "all products" page should lead to brand page.
 * SEO shouldn't modify brand filter options URLs for this case.
 */
class CutBrandAttribute
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        ConfigProvider $configProvider,
        RequestInterface $request
    ) {
        $this->configProvider = $configProvider;
        $this->request = $request;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(
        SeoOptions $subject, // @phpstan-ignore class.notFound
        array $seoOptions
    ): array {
        if ($this->isCustomNavigationPage() && ($brandAttributeCode = $this->configProvider->getBrandAttributeCode())) {
            unset($seoOptions[$brandAttributeCode]);
        }

        return $seoOptions;
    }

    private function isCustomNavigationPage(): bool
    {
        /** @var HttpRequest $request */
        $request = $this->request;

        return ($request->getModuleName() === 'amshopby' || $request->getModuleName() === 'ambrand')
            && $request->getControllerName() === 'index'
            && $request->getActionName() === 'index';
    }
}
