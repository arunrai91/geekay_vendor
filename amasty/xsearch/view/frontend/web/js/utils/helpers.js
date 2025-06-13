/**
 * Amasty Search helpers
 */

define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Catalog/js/catalog-add-to-cart',
    'mage/cookies'
], function ($, ko, _) {
    'use strict';

    /**
     * Update ko subscribe method with silent possibilities
     *
     * @param {Object} value
     * @returns {void}
     */
    ko.observable.fn.silentUpdate = function (value) {
        this.notifySubscribers = function () {};
        this(value);

        this.notifySubscribers = function () {
            ko.subscribable.fn.notifySubscribers.apply(this, arguments);
        };
    };

    return {
        viewport: {
            height: $(window).height(),
            width: $(window).width(),
            page_wrapper_height: $('.page-wrapper')[0].getBoundingClientRect().height
        },
        constants: {
            mobile_view: 768
        },
        regex: {
            tags: /(<([^>]+)>)/gi
        },
        selectors: {
            formKeyInput: 'input[name="form_key"]',
            addToCartForm: '[data-role="tocart-form"]'
        },
        formKey: $.mage.cookies.get('form_key'),

        /**
         * Update Form Key
         *
         * @param {Object} node
         *
         * @desc Updating inner form key inserting
         * @return {void}
         */
        updateFormKey: function (node) {
            var self = this,
                formKeyInput = $(node).find(self.selectors.formKeyInput);

            if (formKeyInput.val() !== self.formKey) {
                formKeyInput.val(self.formKey);
            }
        },

        /**
         * Initialize add to cart form handler
         *
         * @param {Object|Array} node
         * @return {void}
         */
        initProductAddToCart: function (node) {
            var form = $(node).find(this.selectors.addToCartForm);

            form.catalogAddToCart({});
        },

        /**
         * Strip all HTML Tags from target string
         *
         * @public
         * @param {String} string target
         * @return {Boolean|String}
         */
        stripTags: function (string) {
            if (this.regex.tags.test(string)) {
                return string.replace(this.regex.tags, '');
            }

            return false;
        },

        /**
         * Components Array initialization and setting in target component
         *
         * @public
         * @param {Array} array target uiClasses
         * @param {Object} component current uiClass
         * @return {void}
         */
        initComponentsArray: function (array, component) {
            _.each(array, function (item) {
                component[item.index] = item;
            });
        },

        /**
         * Applying Bindings in target node element
         *
         * @public
         * @param {Object} element - node element
         * @param {Object} context - current context
         * @return {void}
         */
        applyBindings: function (element, context) {
            _.defer(function () {
                ko.applyBindingsToDescendants(context, element);
                $(element).trigger('contentUpdated');
            });
        },

        /**
         * Setting result block position via available view port
         *
         * @public
         * @param {Object} node - node element
         * @return {Boolean | void} - if set
         */
        setNodePositionByViewport: _.debounce(function (node) {
            var rect = _.clone(node.getBoundingClientRect()),
                nodePosition = $(node).offset(),
                inViewPort = this._inViewPort(rect);

            node.style.top = '100%';
            node.style.bottom = 'auto';

            if (this.viewport.page_wrapper_height < nodePosition.top + rect.height) {
                node.style.top = 'auto';
                node.style.bottom = '100%';
            }

            if (!_.isBoolean(inViewPort)) {
                node.style[inViewPort] = 0;
            }
        }),

        /**
         * Checker if current node in available view port
         *
         * @private
         * @param {Object} rect - cloned current rect parameters of result node
         * @return {Boolean | String} - true | key of parameter which is not in view port
         */
        _inViewPort: function (rect) {
            var result = true;

            Object.keys(rect).map(function (key) {
                if (Math.sign(rect[key]) === -1 && !_.includes(['y', 'x'], key)) {
                    result = key;

                    return key;
                }
            });

            return result;
        }
    };
});
