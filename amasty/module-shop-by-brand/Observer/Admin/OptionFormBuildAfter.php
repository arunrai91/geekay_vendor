<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Observer\Admin;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopbyBase\Model\OptionSettings\Save;
use Amasty\ShopbyBase\Model\OptionSettings\UrlResolver;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Catalog\Model\Category\Attribute\Source\Page;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Config\Model\Config\Source\Yesno as YesNoSource;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form;
use Magento\Framework\Event\ObserverInterface;

class OptionFormBuildAfter implements ObserverInterface
{
    private const UPGRADE_SUBSCRIPTION_PLAN_LINK = 'https://amasty.com/amcustomer/account/products/'
        . '?utm_source=extension&utm_medium=backend&utm_campaign=subscribe_shopbybrand';
    /**
     * @var Page
     */
    private $page;

    /**
     * @var Config
     */
    private $wysiwygConfig;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlResolver
     */
    private $optionsUrlResolver;

    /**
     * @var YesNoSource
     */
    private $yesnoSource;

    public function __construct(
        Page $page,
        ConfigProvider $configProvider,
        Config $wysiwygConfig,
        UrlResolver $optionsUrlResolver,
        YesNoSource $yesnoSource = null// TODO move to not optional
    ) {
        $this->page = $page;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->configProvider = $configProvider;
        $this->optionsUrlResolver = $optionsUrlResolver;
        $this->yesnoSource = $yesnoSource ?? ObjectManager::getInstance()->get(YesNoSource::class);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Form $form */
        $form = $observer->getData('form');

        /** @var OptionSetting $setting */
        $setting = $observer->getData('setting');
        $storeId = $observer->getData('store_id');
        $attributeCode = $setting->getAttributeCode();
        $isBrandAttributeCode = $attributeCode === $this->configProvider->getBrandAttributeCode($storeId);

        $this->addMetaDataFieldset($form);
        $this->addProductListFieldset($form, $setting, (int) $storeId, $isBrandAttributeCode);
        $this->addOtherFieldset($observer, $isBrandAttributeCode);
    }

    /**
     * @param Form $form
     */
    private function addMetaDataFieldset(\Magento\Framework\Data\Form $form)
    {
        $metaDataFieldset = $form->addFieldset(
            'meta_data_fieldset',
            [
                'legend' => __('Meta Data'),
                'class'=>'form-inline'
            ]
        );

        $metaDataFieldset->addField(
            'meta_title',
            'text',
            [
                'name' => 'meta_title',
                'label' => __('Meta Title'),
                'title' => __('Meta Title')
            ]
        );

        $metaDataFieldset->addField(
            'meta_description',
            'textarea',
            ['name' => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description')
            ]
        );

        $metaDataFieldset->addField(
            'meta_keywords',
            'textarea',
            [
                'name' => 'meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords')
            ]
        );
    }

    private function addProductListFieldset(
        \Magento\Framework\Data\Form $form,
        OptionSetting $model,
        int $storeId,
        bool $isBrandAttributeCode
    ): void {
        $productListFieldset = $form->addFieldset(
            'product_list_fieldset',
            [
                'legend' => __('Page Content'),
                'class'=>'form-inline'
            ]
        );

        $productListFieldset->addField(
            'title',
            'text',
            ['name' => 'title', 'label' => __('Page Title'), 'title' => __('Title')]
        );

        $productListFieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'wysiwyg' => true,
                'config' => $this->wysiwygConfig->getConfig(['add_variables' => false]),
            ]
        );

        $categoryImage = '';
        $categoryImageUseDefault = $model->getData('image_use_default') && $model->getCurrentStoreId();
        if ($url = $this->optionsUrlResolver->resolveImageUrl($model)) {
            $categoryImage = '
            <div>
            <br>
            <input type="checkbox" id="image_delete" name="' . Save::IMAGE_DELETE . '" value="1" ' .
                ($categoryImageUseDefault ? 'disabled="disabled"' : '' ).
            ' />
            <label for="image_delete">' . __('Delete Image') . '</label>
            <br>
            <br><img src="'.$url.'" ' .($categoryImageUseDefault ? 'style="display:none"' : ''). ' alt="Current Image"/>
            </div>';
        }

        $productListFieldset->addField(
            'image',
            'file',
            [
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
                'after_element_html'=>$categoryImage
            ]
        );

        if ($isBrandAttributeCode) {
            $productListFieldset->addField(
                'short_description',
                'textarea',
                [
                    'name' => 'short_description',
                    'label' => __('Short Description'),
                    'title' => __('Short Description')
                ],
                'description'
            );

            $productListFieldset->addField(
                OptionSettingInterface::IMAGE_ALT,
                'text',
                [
                    'name' => OptionSettingInterface::IMAGE_ALT,
                    'label' => __('Image Alt'),
                    'title' => __('Image Alt'),
                    'note' => __('Image Alt will be used for the brand image on the brand page')
                ],
                'image'
            );
        }

        $listCmsBlocks = $this->page->toOptionArray();

        $productListFieldset->addField(
            'top_cms_block_id',
            'select',
            [
                'name' => 'top_cms_block_id',
                'label' => __('Top CMS Block'),
                'title' => __('Top CMS Block'),
                'values' => $listCmsBlocks
            ]
        );

        $productListFieldset->addField(
            'bottom_cms_block_id',
            'select',
            [
                'name' => 'bottom_cms_block_id',
                'label' => __('Bottom CMS Block'),
                'title' => __('Bottom CMS Block'),
                'values' => $listCmsBlocks
            ]
        );

        $productListFieldset->addField(
            OptionSettingInterface::SHOW_BRAND_INFO,
            'select',
            [
                'name' => OptionSettingInterface::SHOW_BRAND_INFO,
                'label' => __('Display Additional Brand Information'),
                'title' => __('Display Additional Brand Information'),
                'values' => $this->yesnoSource->toOptionArray(),
                'disabled' => true,
                'note' => sprintf(
                    'The functionality is available as part of an active product subscription or support '
                    . 'subscription. To upgrade and obtain functionality please follow the '
                    . '<a href="%s" target="_blank">link</a>.<br>'
                    . 'Than you can find the \'%s\' package for installation in composer suggest.',
                    self::UPGRADE_SUBSCRIPTION_PLAN_LINK,
                    'amasty/module-shop-by-brand-subscription-functionality'
                )
            ],
            OptionSettingInterface::BOTTOM_CMS_BLOCK_ID
        );
    }

    private function addOtherFieldset(\Magento\Framework\Event\Observer $observer, bool $isBrandAttributeCode): void
    {
        /** @var \Amasty\ShopbyBase\Model\OptionSetting $model */
        $model = $observer->getData('setting');

        $img = $this->optionsUrlResolver->resolveSliderImageUrl($model, true);
        $sliderImage = '';
        $imageUseDefault = $model->getData('slider_image_use_default') && $model->getCurrentStoreId();
        if ($img) {
            $sliderImage = '
            <div><br>
            <input type="checkbox" id="slider_image_delete" name="' . Save::SLIDER_IMAGE_DELETE . '" value="1" ' .
                (($imageUseDefault) ? 'disabled="disabled"' : '' ).
                ' />
            <label for="slider_image_delete">' . __('Delete Image') . '</label>
            <br><br>
            <img src="' . $img . '"  alt="Current Image" style="' . ($imageUseDefault ? 'display:none;"' : '"') . '/>
            </div>';
        }

        $note = $isBrandAttributeCode
            ? __('Used in Brands Slider, Product Page Icon & Swatch for Multiselect Attribute.')
            : __('Used as Product Page Icon & Swatch for Multiselect Attribute.');
        $smallImageAltNote = $isBrandAttributeCode
            ? __('Small Image Alt will be used for the brand image in brand pop-up, brand slider, on'
                . ' all brands and product pages.')
            : __('Small Image Alt will be used for the Product Page Icon Image & Swatch Image for'
                . ' Multiselect Attribute.');

        $form = $observer->getData('form');
        $featuredFieldset = $form->addFieldset('other_fieldset', ['legend' => __('Other'), 'class'=>'form-inline']);
        $featuredFieldset->addField(
            'slider_image',
            'file',
            [
                'name' => 'slider_image',
                'label' => __('Small Image'),
                'title' => __('Small Image'),
                'note'  => $note,
                'after_element_html'=>$sliderImage
            ]
        );

        $featuredFieldset->addField(
            OptionSettingInterface::SMALL_IMAGE_ALT,
            'text',
            [
                'name' => OptionSettingInterface::SMALL_IMAGE_ALT,
                'label' => __('Small Image Alt'),
                'title' => __('Small Image Alt'),
                'note' => $smallImageAltNote
            ]
        );
    }
}
