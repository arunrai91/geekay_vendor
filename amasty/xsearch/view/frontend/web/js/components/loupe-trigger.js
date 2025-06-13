/**
 * Amasty Header Loupe Trigger Search Widget
 * for mobile devices and type full width
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'uiRegistry',
    'amsearch_helpers'
], function ($, ko, Component, registry, helpers) {
    'use strict';

    return Component.extend({
        defaults: {
            components: [
                'index = amsearch_wrapper'
            ]
        },

        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();

            registry.get(this.components, function () {
                helpers.initComponentsArray(arguments, this);

            }.bind(this));

            return this;
        },

        /**
         * Toggling Search Menu
         *
         * @public
         */
        toggle: function () {
            this.amsearch_wrapper.focused(!this.amsearch_wrapper.focused());
        }
    });
});
