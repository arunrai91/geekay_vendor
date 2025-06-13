<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\ViewModel;

use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;
use Amasty\ShopbyBase\Model\OptionSettings\UrlResolver;
use Amasty\ShopbyBrand\Model\BrandResolver;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BrandView implements ArgumentInterface
{
    /**
     * @var BrandResolver
     */
    private $brandResolver;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var Resolver
     */
    private $layerResolver;

    public function __construct(
        BrandResolver $brandResolver,
        UrlResolver $urlResolver,
        Resolver $layerResolver
    ) {
        $this->brandResolver = $brandResolver;
        $this->urlResolver = $urlResolver;
        $this->layerResolver = $layerResolver;
    }

    /**
     * Image URL resolver
     */
    public function getImageUrl(): ?string
    {
        $brand = $this->brandResolver->getCurrentBrand();
        $category = $this->getCurrentCategory();

        if ($brand === null) {
            return null;
        } elseif ($image = $category->getData(CategoryManager::CATEGORY_SHOPBY_IMAGE_URL)) {
            return $image;
        }

        return $this->urlResolver->resolveImageUrl($brand);
    }

    /**
     * Image ALT tag text resolver
     */
    public function getImageAlt(): string
    {
        $brand = $this->brandResolver->getCurrentBrand();
        if ($brand === null) {
            return '';
        }

        if ($brand->getImageAlt()) {
            return $brand->getImageAlt();
        }

        return $brand->getAttributeOption()->getLabel();
    }

    private function getCurrentCategory(): Category
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }

    public function getBrandTitle(): string
    {
        $brand = $this->brandResolver->getCurrentBrand();
        if ($brand === null) {
            return '';
        }

        return $brand->getTitle();
    }
}
