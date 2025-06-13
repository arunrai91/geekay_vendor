<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab\Content;

use Amasty\Feed\Model\Category\ResourceModel\CollectionFactory;
use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Amasty\Feed\Model\GoogleWizard\Element as GoogleWizardElement;
use Amasty\Feed\Model\RegistryContainer;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Amasty\Feed\Model\Export\Product as ExportProduct;

class Element extends Template implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'googlewizard/content.phtml';

    /**
     * @var ExportProduct
     */
    protected $export;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    public function __construct(
        Context $context,
        CollectionFactory $categoryCollectionFactory,
        ExportProduct $export,
        array $data = []
    ) {
        $this->export = $export;
        parent::__construct($context, $data);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Render element
     *
     * Render element for Basic and Optional steps.
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);

        return $this->toHtml();
    }

    /**
     * Get types of field
     *
     * @return array
     */
    public function getFieldTypes()
    {
        $types = [
            RegistryContainer::TYPE_ATTRIBUTE  => __('Attribute'),
            RegistryContainer::TYPE_IMAGE  => __('Images'),
            RegistryContainer::TYPE_TEXT  => __('Text')
        ];

        return $types;
    }

    /**
     * Check element is selected by type
     *
     * @param GoogleWizardElement $element
     * @param string $type
     * @return boolean
     */
    public function isSelectedType($element, $type)
    {
        return $element->getType() == $type;
    }

    /**
     * Check element is selected by attribute
     *
     * @param GoogleWizardElement $element
     * @param string $value
     * @return bool
     */
    public function isSelectedAttribute($element, $value)
    {
        return $element->getValue() == $value;
    }

    /**
     * Get value of attribute
     *
     * @param GoogleWizardElement $element
     * @return string
     */
    public function getAttributeValue($element)
    {
        return $element->getValue();
    }

    /**
     * Get all attributes
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return [
            'basic' => [
                'label' => __("Basic"),
                'options' => $this->getBasicAttributes()
            ],
            'product' => [
                'label' => __("Product"),
                'options' => $this->getProductAttributes()
            ],
            'price' => [
                'label' => __("Inventory"),
                'options' => $this->getPriceAttributes()
            ],
            'category' => [
                'label' => __("Category"),
                'options' => $this->getCategoryAttributes()
            ],
            'image' => [
                'label' => __("Image"),
                'options' => $this->getImageAttributes()
            ],
            'gallery' => [
                'label' => __("Gallery"),
                'options' => $this->getGalleryAttributes()
            ],
            'url' => [
                'label' => __("Url"),
                'options' => $this->getUrlAttributes()
            ],
            'other' => [
                'label' => __('Other'),
                'options' => $this->getOtherAttributes()
            ]
        ];
    }

    /**
     * Get basic attributes
     *
     * @return array
     */
    public function getBasicAttributes(): array
    {
        return [
            FeedAttributesStorage::PREFIX_BASIC_ATTRIBUTE . '|sku' => __('SKU'),
            FeedAttributesStorage::PREFIX_BASIC_ATTRIBUTE . '|product_type' => __('Type'),
            FeedAttributesStorage::PREFIX_BASIC_ATTRIBUTE . '|product_websites' => __('Websites'),
            FeedAttributesStorage::PREFIX_BASIC_ATTRIBUTE . '|created_at' => __('Created'),
            FeedAttributesStorage::PREFIX_BASIC_ATTRIBUTE . '|updated_at' => __('Updated'),
            FeedAttributesStorage::PREFIX_BASIC_ATTRIBUTE . '|product_id' => __('Product ID'),
        ];
    }

    /**
     * Get category attributes
     *
     * @return array
     */
    public function getCategoryAttributes()
    {
        $attributes = [
            FeedAttributesStorage::PREFIX_CATEGORY_ATTRIBUTE . '|category' => __(
                'Default'
            ),
        ];

        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addOrder('name');
        foreach ($categoryCollection->getItems() as $category) {
            $attributes[FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_ATTRIBUTE . '|'
            . $category->getCode()]
                = $category->getName();
        }

        return $attributes;
    }

    /**
     * Get image attributes
     *
     * @return array
     */
    public function getImageAttributes()
    {
        return [
            FeedAttributesStorage::PREFIX_IMAGE_ATTRIBUTE . '|thumbnail'   => __('Thumbnail'),
            FeedAttributesStorage::PREFIX_IMAGE_ATTRIBUTE . '|image'       => __('Base Image'),
            FeedAttributesStorage::PREFIX_IMAGE_ATTRIBUTE . '|small_image' => __('Small Image'),
        ];
    }

    /**
     * Get gallery attributes
     *
     * @return array
     */
    public function getGalleryAttributes()
    {
        return [
            FeedAttributesStorage::PREFIX_GALLERY_ATTRIBUTE . '|image_1' => __('Image 1'),
            FeedAttributesStorage::PREFIX_GALLERY_ATTRIBUTE . '|image_2' => __('Image 2'),
            FeedAttributesStorage::PREFIX_GALLERY_ATTRIBUTE . '|image_3' => __('Image 3'),
            FeedAttributesStorage::PREFIX_GALLERY_ATTRIBUTE . '|image_4' => __('Image 4'),
            FeedAttributesStorage::PREFIX_GALLERY_ATTRIBUTE . '|image_5' => __('Image 5'),
        ];
    }

    /**
     * Get price attributes
     *
     * @return string[]
     */
    public function getPriceAttributes(): array
    {
        return [
            FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE . '|price' => __('Price'),
            FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE . '|final_price' => __('Final Price'),
            FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE . '|regular_price' => __('Regular Price'),
            FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE . '|min_price' => __('Min Price'),
            FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE . '|max_price' => __('Max Price'),
            FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE . '|tax_price' => __('Price with TAX(VAT)'),
            FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE . '|tax_final_price' => __('Final Price with TAX(VAT)'),
            FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE . '|tax_min_price' => __('Min Price with TAX(VAT)'),
            FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE . '|special_price' => __('Special Price'),
            FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE . '|grouped_price' => __('Grouped Total Price')
        ];
    }

    /**
     * Get url attributes
     *
     * @return array
     */
    public function getUrlAttributes()
    {
        return [
            FeedAttributesStorage::PREFIX_URL_ATTRIBUTE . '|short'         => __('Short'),
            FeedAttributesStorage::PREFIX_URL_ATTRIBUTE . '|with_category' => __('With Category'),
        ];
    }

    /**
     * Get product attributes
     *
     * @return array
     */
    public function getProductAttributes()
    {
        $attributes = [];
        $codes = $this->export->getExportAttrCodesList();

        foreach ($codes as $code => $title) {
            $attributes[FeedAttributesStorage::PREFIX_PRODUCT_ATTRIBUTE . "|" . $code] = $title;
        }

        return $attributes;
    }

    /**
     * Get custom (not-classified) attributes
     *
     * @return array
     */
    public function getOtherAttributes()
    {
        return [
            FeedAttributesStorage::PREFIX_OTHER_ATTRIBUTES . '|tax_percents' => __('Tax Percents'),
            FeedAttributesStorage::PREFIX_OTHER_ATTRIBUTES . '|sale_price_effective_date' => __(
                'Sale Price Effective Date'
            )
        ];
    }
}
