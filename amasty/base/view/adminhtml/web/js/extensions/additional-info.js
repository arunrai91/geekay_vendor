define([
    'uiComponent',
    'jquery',
    'mage/url',
    'mage/loader'
], function (Component, $, urlBuilder) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Base/extensions/additional-info',
            contentEndpoint: null,
            content: '',
            isLoading: true
        },

        /**
         * @returns {Object}
         */
        initialize: function () {
            this._super();
            this.getContent();

            return this;
        },

        /**
         * @returns {Object}
         */
        initObservable: function () {
            return this._super()
                .observe(['content', 'isLoading']);
        },

        /**
         * @returns {jqXHR}
         */
        getContent: function () {
            return $.get(
                urlBuilder.build(this.contentEndpoint),
                ({underProductsContentHtml}) => {
                    const content = typeof underProductsContentHtml === undefined
                        ? ''
                        : underProductsContentHtml;

                    this.isLoading(false);
                    this.content(content);
                });
        }
    });
});
