define([
    'Amasty_Base/js/form/element/ui-promotion-select'
], function (uiPromotionSelect) {
    return uiPromotionSelect.extend({
        defaults: {
            listens: {
                value: 'executeSubmit'
            },
            modules: {
                form: 'amfeed_wizard_form.areas'
            }
        },

        executeSubmit: function (value) {
            if (!!value.length) {
                // setTimeout for compatibility with Magento 2.4.3 and less.
                // For old jQuery version there is difference in then(save) function
                setTimeout(function () {
                    this.form().submit(true);
                }.bind(this), 0);
            }
        }
    });
});
