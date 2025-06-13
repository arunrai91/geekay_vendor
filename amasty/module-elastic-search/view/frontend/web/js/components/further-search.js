/**
 * Amasty Elastic Search Further Search UI Component
 * amelsearch_further_wrapper
 */

define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            templates: {
                clear_button: 'Amasty_ElasticSearch/further_search/clear_button.html',
                loupe_button: 'Amasty_ElasticSearch/further_search/loupe_button.html',
                submit_button: 'Amasty_ElasticSearch/further_search/submit_button.html'
            }
        },

        /**
         * @inheritDoc
         */
        initObservable: function () {
            this._super()
                .observe({
                    focused: false,
                    value: this.defaultValue
                });

            this.focused.extend({
                rateLimit: {
                    method: 'notifyWhenChangesStop',
                    timeout: 100
                }
            });

            return this;
        },

        /**
         * Input event 'enter keydown' handle
         *
         * @public
         * @params {node} Object
         * @params {event} Object
         * @return {Boolean} for propagation
         */
        onEnter: function (node, event) {
            if (event.keyCode === 13) {
                this.search();
            }

            return true;
        },

        /**
         * On search process handle
         *
         * @public
         * @return {void}
         */
        search: function () {
            window.location = this.searchUrl + (this.searchUrl.indexOf("?") > 0 ? "&" : "?")
                + 'sub_query=' + this.value();
        }
    });
});
