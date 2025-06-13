var config = {
    config: {
        mixins: {'Amasty_Promo/js/form': {
                'Amasty_Rules/js/form-mixin': true
            }
        }
    },
    shim: {
        'Amasty_Promo/js/form': {
            deps: ['Amasty_Rules/js/form-mixin']
        }
    }
}
