define([
    'jquery',
    'mage/url',
    'mage/cookies',
    'domReady!'
], function ($, urlBuilder) {
    'use strict';

    $.widget('mage.amXsearchCollectProductView', {
        options: {
            backendUrl: 'amasty_xsearch/analytics/collect'
        },
        selectors: {
            productId: '.product-add-form [name="product"]'
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            var productId = this.getProductId();
            urlBuilder.setBaseUrl(window.BASE_URL);

            if (!isNaN(productId)) {
                this.logProductView(productId);
            }
        },

        /**
         * @return {number|NaN}
         */
        getProductId: function () {
            var result = NaN,
                element = $(this.selectors.productId);

            if (element.length) {
                result = element.attr('value');
            }

            return result;
        },

        /**
         * @param {number} productId
         */
        logProductView: function (productId) {
            $.ajax({
                url: urlBuilder.build(this.options.backendUrl),
                method: 'GET',
                data: {
                    form_key: $.mage.cookies.get('form_key'),
                    telemetry: [{ type: 'product_view', product_id: productId }]
                }
            });
        }
    });

    return $.mage.amXsearchCollectProductView;
});
