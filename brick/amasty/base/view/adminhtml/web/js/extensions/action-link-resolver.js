define([
    'mage/translate'
], function ($t) {
    'use strict';

    /**
     * @typedef {Object} ActionLinkData
     * @property {string|null} text
     * @property {string|null} url
     */

    return {
        statuses: {
            checkSubscription: ['warning', 'error'],
            notFound: 'not found'
        },
        subscriptionUrl: 'https://amasty.com/amasty_recurring/customer/subscriptions',

        /**
         * @param {ModuleInfo} module
         * @return {ActionLinkData}
         */
        getActionLinkData: function (module) {
            let actionLinkData = {
                text: null,
                url: null
            }

            if (module.upgrade_url) {
                actionLinkData = {
                    text: $t('Upgrade Your Plan'),
                    url: module.upgrade_url
                }
            }

            if (this.isNeedCheckSubscription(module)) {
                actionLinkData = {
                    text: $t('Check Your Subscriptions'),
                    url: this.subscriptionUrl
                }
            }

            if (this.isModuleNotFound(module)) {
                actionLinkData = {
                    text: $t('Buy a License'),
                    url: module.module_url
                }
            }

            return actionLinkData;
        },

        /**
         * @param {ModuleInfo} module
         * @returns {boolean}
         */
        isNeedCheckSubscription: function (module) {
            return this.statuses.checkSubscription.includes(module.verify_status?.type);
        },

        /**
         * @param {ModuleInfo} module
         * @returns {boolean}
         */
        isModuleNotFound: function (module) {
            return module.verify_status?.status.toLowerCase() === this.statuses.notFound;
        }
    }
});
