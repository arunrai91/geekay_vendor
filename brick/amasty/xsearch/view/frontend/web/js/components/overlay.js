/**
 * Amasty Xsearch Overlay UI Component
 * amsearch_overlay_section
 */

define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            selectors: {
              body: 'body'
            },
            classes: {
                opened: '-amsearch-overlay-opened'
            },
            nodes: {}
        },

        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();

            this.nodes.body = $(this.selectors.body);

            return this;
        },

        /**
         * @inheritDoc
         */
        initObservable: function () {
            this._super()
                .observe({
                    opened: false
                });

            return this;
        },

        /**
         * Initialize wrapper node
         *
         * @public
         */
        initNode: function (node) {
            this.nodes.wrapper = $(node.parentNode);
        },

        /**
         * Show wrapper node
         *
         * @public
         */
        show: function () {
            this.nodes.body.addClass(this.classes.opened);
            this.nodes.wrapper.show();
            this.opened(true);
        },

        /**
         * Hide wrapper node
         *
         * @public
         */
        hide: function () {
            this.nodes.body.removeClass(this.classes.opened);
            this.nodes.wrapper.hide();
            this.opened(false);
        }
    });
});
