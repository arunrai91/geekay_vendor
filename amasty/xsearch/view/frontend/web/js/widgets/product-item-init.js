define([
    'jquery',
    'Amasty_Xsearch/js/utils/helpers',
    'amsearchProductLinksStorage'
], function ($, helpers, productLinksStorage) {
    'use strict';

    $.widget('mage.amsearchProductItemInit', {

        /**
         * @inheritDoc
         */
        _create: function () {
            helpers.updateFormKey(this.element);
            helpers.initProductAddToCart(this.element);
            productLinksStorage.bindLinks(this.element);
            $('body').trigger('amsearch.popup.contentUpdated', this.element);

            return this;
        }
    });

    return $.mage.amsearchProductItemInit;
});
