<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Widget;

use Amasty\Mostviewed\Api\GroupRepositoryInterface;
use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\Group;
use Amasty\Mostviewed\Model\ProductProvider;
use Amasty\Mostviewed\Model\Validation\Group\GroupValidator;
use Amasty\Mostviewed\ViewModel\Related\ShiftResolverViewModel;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Amasty\Mostviewed\Helper\Quote;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Url\EncoderInterface;

class Related extends AbstractProduct implements IdentityInterface, BlockInterface
{
    public const CACHE_TAG = 'amasty_mostviewed';
    public const IMAGE_TYPE = 'related_products_sidebar';

    /**
     * @var null|ProductCollection
     */
    private ?ProductCollection $productCollection = null;

    /**
     * @var GroupRepositoryInterface
     */
    private GroupRepositoryInterface $groupRepository;

    /**
     * @var ProductProvider
     */
    private ProductProvider $productProvider;

    /**
     * @var Visibility
     */
    private Visibility $catalogProductVisibility;

    /**
     * @var Quote
     */
    private Quote $quoteHelper;

    /**
     * @var ConfigProvider|null
     */
    private ConfigProvider $configProvider;

    /**
     * @var ShiftResolverViewModel|null
     */
    private ShiftResolverViewModel $shiftResolverViewModel;

    /**
     * @var EncoderInterface|null
     */
    private ?EncoderInterface $urlEncoder;

    /**
     * @var GroupValidator|null
     */
    private ?GroupValidator $groupValidator;

    public function __construct(
        GroupRepositoryInterface $groupRepository,
        ProductProvider $productProvider,
        Visibility $catalogProductVisibility,
        Quote $quoteHelper,
        Context $context,
        array $data = [],
        ?ConfigProvider $configProvider = null,
        ?ShiftResolverViewModel $shiftResolverViewModel = null,
        ?EncoderInterface $urlEncoder = null,
        ?GroupValidator $groupValidator = null
    ) {
        parent::__construct($context, $data);
        $this->groupRepository = $groupRepository;
        $this->productProvider = $productProvider;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->quoteHelper = $quoteHelper;
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
        $this->shiftResolverViewModel = $shiftResolverViewModel
            ?? ObjectManager::getInstance()->get(ShiftResolverViewModel::class);
        $this->urlEncoder = $urlEncoder
            ?? ObjectManager::getInstance()->get(EncoderInterface::class);
        $this->groupValidator = $groupValidator
            ?? ObjectManager::getInstance()->get(GroupValidator::class);
    }

    /**
     * @return ProductCollection|null
     */
    public function getProductCollection()
    {
        if ($this->productCollection === null) {
            $initialShift = 0;
            if ($this->getPosition()) {
                $initialShift = $this->shiftResolverViewModel->getShift($this->getPosition());
            }

            $this->initializeProductCollection($initialShift);

            if ($this->getPosition()) {
                $this->shiftResolverViewModel->incrementDisplayedBlockCount($this->getPosition());
            }
        }

        return $this->productCollection;
    }

    public function clearProductCollection()
    {
        $this->productCollection = null;
    }

    /**
     * @param ProductInterface $product
     * @param $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        $requestingPageUrl = $this->getRequest()->getParam('requesting_page_url');

        if (null !== $requestingPageUrl) {
            $additional['useUencPlaceholder'] = true;

            return str_replace(
                '%25uenc%25',
                $this->urlEncoder->encode($requestingPageUrl),
                parent::getAddToCartUrl($product, $additional)
            );
        }

        return parent::getAddToCartUrl($product, $additional);
    }

    private function initializeProductCollection(?int $shift): void
    {
        if ($shift === null) {
            return;
        }

        $entity = $this->getEntity();
        $entityId = $entity ? $entity->getId() : $this->getEntityId();

        $group = $this->getCurrentGroup($entityId, $shift);
        if ($group && $group->getMaxProducts()) {
            $this->setGroupId($group->getId());
            $this->setTitle($group->getBlockTitle());
            $this->setAddToCart($group->getAddToCart());
            $this->setDisplayButtonWishlist($group->getDisplayWishlistButton());
            $this->setDisplayButtonCompare($group->getDisplayCompareButton());
            $this->setBlockLayout($group->getBlockLayout());

            $this->productCollection = $this->productProvider->getAppliedProducts($group, $entity);
            if ($this->productCollection) {
                $this->productCollection->setPageSize($group->getMaxProducts());
                $productId = $entity instanceof ProductInterface ? (int)$entityId : null;
                $this->productProvider->prepareCollection($group, $this->productCollection, $productId);
            }
            if ($this->configProvider->isEnabledSubsequentRules()
                && (!$this->productCollection || !$this->productCollection->getSize())
                && $group->getBlockPosition() !== BlockPosition::CUSTOM
            ) {
                if ($this->getPosition()) {
                    $shift = $this->shiftResolverViewModel->getShift($this->getPosition());
                } else {
                    $shift++;
                }

                if ($this->getMaxRetries() !== null && $shift > $this->getMaxRetries()) {
                    $shift = null; // break loop
                }

                $this->initializeProductCollection($shift);
            } elseif ($this->productCollection) {
                $this->setData('resolved_group', $group);
                $this->setData('resolved_entity', $entity);
                foreach ($this->productCollection->getItems() as $product) {
                    $product->setDoNotUseCategoryId(true);
                }
            }
        }
    }

    /**
     * @param int|null $entityId
     * @param int $shift
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface|bool
     */
    protected function getCurrentGroup($entityId, int $shift)
    {
        if ($this->hasData('current_group')) {
            return $this->getData('current_group');
        }

        $group = false;
        if ($this->getGroupId()) { //custom block
            try {
                $group = $this->groupRepository->getById($this->getGroupId());
                if (!$this->groupValidator->validateGroup($group)) {
                    return false;
                }

                //do not show if position was changed in group configuration
                if ($group && $group->getBlockPosition() !== BlockPosition::CUSTOM) {
                    $group = false;
                }
            } catch (NoSuchEntityException $ex) {
                $group = false;
            }
        }

        $position = $this->getPosition();
        if (!$group && $entityId && $position !== null) {
            $group = $this->groupRepository->getGroupByIdAndPosition($entityId, $position, $shift);
        }

        return $group;
    }

    /**
     * @return ProductModel|CategoryModel
     */
    private function getEntity()
    {
        switch ($this->_request->getFullActionName()) {
            case 'catalog_product_view':
                $entity = $this->_coreRegistry->registry('current_product');
                break;
            case 'catalog_category_view':
                $entity = $this->_coreRegistry->registry('current_category');
                break;
            case 'checkout_cart_index':
                $entity = $this->quoteHelper->getLastAddedProductInCart();
                break;
            default:
                $entity = null;
        }

        if (!$entity) {
            $entity = $this->getData('entity');
        }

        return $entity;
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        $class = 'widget';

        if (in_array(
            $this->getPosition(),
            [BlockPosition::CART_BEFORE_CROSSSEL, BlockPosition::CART_AFTER_CROSSSEL]
        )) {
            $class = 'crosssell';
        }

        return $class;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [
            self::CACHE_TAG . '_' . $this->getPosition(),
            Group::CACHE_TAG . '_' . $this->getGroupId()
        ];
    }

    /**
     * Logo added by plugin into ShopbyBrand module
     *
     * @param $product
     * @return string
     */
    public function getBrandLogoHtml($product)
    {
        return '';
    }

    public function getImageModel(ProductInterface $product): Image
    {
        return $this->_imageHelper->init($product, self::IMAGE_TYPE);
    }

    /**
     * @return array [attributeName => attributeValue, ...]
     */
    public function getAdditionalAttributes(): array
    {
        return [];
    }

    public function getAdditionalAttributesHtml(): string
    {
        $result = '';
        foreach ($this->getAdditionalAttributes() as $attributeName => $attributeValue) {
            $result .= sprintf(
                '%s="%s" ',
                $this->_escaper->escapeHtmlAttr($attributeName),
                $this->_escaper->escapeHtmlAttr($attributeValue)
            );
        }

        return $result;
    }

    public function getResolvedGroup(): ?Group
    {
        return $this->getData('resolved_group');
    }

    /**
     * @return ProductModel|CategoryModel
     */
    public function getResolvedEntity(): ?AbstractModel
    {
        return $this->getData('resolved_entity');
    }

    private function getMaxRetries(): ?int
    {
        return $this->hasData('max_retries') ? (int)$this->getData('max_retries') : null;
    }
}
