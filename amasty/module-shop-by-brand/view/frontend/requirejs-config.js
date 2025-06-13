var config = {
    map: {
        '*': {
            amBrandsSearch: 'Amasty_ShopbyBrand/js/components/ambrands-search',
            amBrandsFilterInit: 'Amasty_ShopbyBrand/js/components/ambrands-filter-init',
            amBrandsFilter: 'Amasty_ShopbyBrand/js/brand-filter',
            swiper: 'Amasty_LibSwiperJs/js/vendor/swiper/swiper.min'
        }
    },
    config: {
        mixins: {
            'mage/menu': {
                'Amasty_ShopbyBrand/js/lib/mage/ambrands-menu-mixin': true
            }
        }
    }
};
