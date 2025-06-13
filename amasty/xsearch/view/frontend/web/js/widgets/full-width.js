/**
 * Amasty Full Width Search Widget
 */

define([
    'jquery',
    'uiRegistry',
    'amsearch_helpers'
], function ($, registry, helpers) {
    'use strict';

    $.widget('mage.amsearchFullWidth', {
        components: [
            'index = amsearch_wrapper'
        ],
        classes: {
            open: '-opened'
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            registry.get(this.components, function () {
                helpers.initComponentsArray(arguments, this);

                this.initObservable();
            }.bind(this));
        },


        /**
         * @inheritDoc
         */
        initObservable: function () {
            this.amsearch_wrapper.opened.subscribe(function (value) {
                if (value) {
                    this.element.addClass(this.classes.open);
                } else {
                    this.element.removeClass(this.classes.open);
                }
            }.bind(this));
        }
    });

    return $.mage.amsearchFullWidth;
});
