/**
 * Log Actions Preview Logic
 */
define([
    'jquery',
    'uiElement',
    'Amasty_AdminActionsLog/js/action/notification'
], function ($, Element, notification) {
    'use strict';

    return Element.extend({
        defaults: {
            data: {},
            messages: {},
            type: ''
        },

        /**
         * @inheritDoc
         * @returns {Object}
         */
        initObservable: function () {
            return this._super()
                .observe([
                    'data',
                    'messages'
                ]);
        },

        initialize: function () {
            this._super();

            if (this.messages().isError) {
                notification.add(this.messages().message, this.messages().isError);
            }

            return this;
        }
    });
});
