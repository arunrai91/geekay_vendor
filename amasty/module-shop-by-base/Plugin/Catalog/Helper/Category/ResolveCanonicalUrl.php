<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Plugin\Catalog\Helper\Category;

use Amasty\ShopbyBase\Model\Customizer\Category as CategoryCustomizer;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Framework\Registry;

class ResolveCanonicalUrl
{
    /**
     * @var Registry
     */
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCanonicalUrl(CategoryHelper $subject, string $newCategoryUrl, string $categoryUrl): string
    {
        $category = $this->registry->registry('current_category');
        if ($category && $category->getData(CategoryCustomizer::ORIGINAL_CATEGORY_URL)) {
            return $categoryUrl;
        }

        return $newCategoryUrl;
    }
}
