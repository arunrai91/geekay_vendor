define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'mage/translate'
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            historyUrl: '',
            data: {},
            text: {
                minutes: $t('minutes'),
                hours: $t('hours'),
                seconds: $t('seconds')
            }
        },

        /**
         * @inheritDoc
         * @returns {Object}
         */
        initObservable: function () {
            return this._super()
                .observe([
                    'data'
                ]);
        },

        /**
         * Get Log History
         *
         * @param {string} actionName
         * @param {string} id
         */
        getHistory: function (actionName, id) {
            $.ajax({
                showLoader: true,
                url: this.historyUrl,
                data: { id: id },
                type: 'GET',
                dataType: 'json'
            }).done(function (response) {
                this.data(response);
                this.openModal();
            }.bind(this));
        },

        /**
         * Get Duration
         *
         * @param {string} timestamp
         * @returns {string}
         */
        getDuration: function (timestamp) {
            var hours = Math.floor(timestamp / 60 / 60),
                minutes = Math.floor(timestamp / 60) - (hours * 60),
                seconds = timestamp % 60,
                duration = [];

            if (hours) {
                duration.push(hours + ' ' + this.text.hours);
            }

            if (minutes) {
                duration.push(minutes + ' ' + this.text.minutes);
            }

            if (seconds) {
                duration.push(seconds + ' ' + this.text.seconds);
            }

            return duration.join(' ');
        }
    });
});
