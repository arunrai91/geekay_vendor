var config = {
    map: {
        '*': {
            amsearchSlick: 'Amasty_Base/vendor/slick/slick.min',
            amsearch_helpers: 'Amasty_Xsearch/js/utils/helpers',
            amsearch_color_helper: 'Amasty_Xsearch/js/utils/color',
            amsearchProductLinksStorage: 'Amasty_Xsearch/js/utils/links-storage',
            amsearchProductSlider: 'Amasty_Xsearch/js/widgets/product-slider',
            amsearchAnalyticsCollector: 'Amasty_Xsearch/js/widgets/analytics-data-collector',
            amsearchCollapsible: 'Amasty_Xsearch/js/widgets/collapsible',
            amsearchFullWidth: 'Amasty_Xsearch/js/widgets/full-width',
            amsearchCollectProductView: 'Amasty_Xsearch/js/widgets/handle-product-view',
            amsearchProductItemInit: 'Amasty_Xsearch/js/widgets/product-item-init',
            amsearchWidgetOverride: 'Amasty_Xsearch/js/content-type/products/appearance/carousel/widget-override.js'
        }
    },
    paths: {
        catalogAddToCart: 'Magento_Catalog/js/catalog-add-to-cart'
    },
    shim: {
        amsearchWidgetOverride: {
            deps: ['Amasty_Base/vendor/slick/slick.min']
        },
        amsearchSlick: {
            deps: ['jquery']
        }
    },
    config: {
        mixins: {
            'Magento_MultipleWishlist/js/multiple-wishlist': {
                'Amasty_Xsearch/js/mixins/multiple-wishlist': true
            },
            'Magento_Theme/js/view/breadcrumbs': {
                'Amasty_Xsearch/js/mixins/breadcrumbs': true
            }
        }
    }
};
