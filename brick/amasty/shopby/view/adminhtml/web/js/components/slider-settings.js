/**
 * Amasty Shopby slider settings Ui Component
 */

define([
    'jquery',
    'uiComponent',
    'amshopby_color',
    'ko'
], function ($, Component, colorHelper, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            shadow: ko.observable(),
            gradients: {
                vertical: ko.observable(),
                horizontal: ko.observable()
            }
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            var self = this;

            self._super()
                .observe({
                    color: self.default.color,
                    style: self.default.style
                });

            self.gradients.vertical(colorHelper.getGradient(self.default.color, 'vertical'));
            self.gradients.horizontal(colorHelper.getGradient(self.default.color, 'horizontal'));
            self.shadow(colorHelper.getShadow(self.default.color));

            return self;
        },

        /**
         * Initialize jQuery Color Picker for target Node
         *
         * @params {Object} node
         */
        initColorPicker: function (node) {
            var self = this;

            $(node).spectrum({
                showAlpha: true,
                preferredFormat: 'rgb',
                showButtons: false,
                color: self.default.color,
                showInput: true,
                hide: function (color) {
                    self._changeColor(color.toRgbString());
                },
                move: function (color) {
                    self._changeColor(color.toRgbString());
                }
            })
        },

        /**
         * Change Current Colors
         *
         * @params {String} color - currentColor
         */
        _changeColor: function (color) {
            this.color(color);
            this.gradients.vertical(colorHelper.getGradient(color, 'vertical'));
            this.gradients.horizontal(colorHelper.getGradient(color, 'horizontal'));
            this.shadow(colorHelper.getShadow(color));
        }
    });
});
