<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Plugin\Amp\Block\Page;

use Amasty\Amp\Block\Page\Head;
use Amasty\Amp\Model\ConfigProvider;
use Amasty\ShopbySeo\Model\Customizer\Category\Seo;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;

class ReplaceAmpCategoryCanonical
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Seo
     */
    private $seoCustomizer;

    public function __construct(
        RequestInterface $request,
        Registry $registry,
        Seo $seoCustomizer
    ) {
        $this->request = $request;
        $this->registry = $registry;
        $this->seoCustomizer = $seoCustomizer;
    }

    public function afterGetCanonicalUrl(
        Head $subject, // @phpstan-ignore class.notFound
        string $url
    ): string {
        $category = $this->registry->registry('current_category');

        // @phpstan-ignore class.notFound
        if ($this->request->getFullActionName() == ConfigProvider::CATALOG_CATEGORY_VIEW) {
            $url = $this->seoCustomizer->getCategoryModeCanonical($category);
        }

        return $url;
    }
}
