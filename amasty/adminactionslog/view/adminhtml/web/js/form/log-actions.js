/**
 * Log Actions Preview Logic
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'Amasty_AdminActionsLog/js/action/notification'
], function ($, Component, notification) {
    'use strict';

    return Component.extend({
        defaults: {
            actionsLogUrl: '',
            messageSelector: '.amaudit-log-modal .page-main-actions',
            messages: {},
            data: {}
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

        /**
         * Get Preview Data
         *
         * @param {string} actionName
         * @param {string} id
         * @returns {void}
         */
        getPreviewData: function (actionName, id) {
            $.ajax({
                showLoader: true,
                url: this.actionsLogUrl,
                data: { element_id: id },
                type: 'GET',
                dataType: 'json'
            }).done(function (response) {
                if (response.isError) {
                    this.messages(response);
                    notification.add(response.message, response.isError, this.messageSelector);
                } else {
                    notification.clear();
                    this.data(this.parseData(response.data));
                }
                this.openModal();
            }.bind(this));
        },

        /**
         * Parse Preview Data
         *
         * @param {Object} data
         * @returns {array}
         */
        parseData: function (data) {
            return Object.keys(data).map(function (key) {
                return data[key];
            });
        }
    });
});
