define([
    'Magento_Ui/js/form/components/fieldset',
    'rjsResolver',
    'Amasty_Base/js/form/element/disabler'
],function (Fieldset, resolver, switcher) {
    'use strict';

    return Fieldset.extend({
        defaults: {
            template: 'Amasty_Base/form/promo-fieldset',
            isPromo: true,
            message: '',
            currentPlanType: 'subscribe',
            planTypes: {
                subscribe: {
                    iconSrc: 'Amasty_Base/images/components/promotion-fieldset/subscribe.svg',
                    iconBgColor: '#ebe7ff',
                    text: 'Subscribe to Unlock'
                },
                upgrade: {
                    iconSrc: 'Amasty_Base/images/components/promotion-fieldset/upgrade.svg',
                    iconBgColor: 'rgba(0, 133, 255, .1)',
                    text: 'Upgrade Your Plan'
                }
            }
        },

        /**
         * @param {Object} elem
         * @returns {void}
         */
        initElement: function (elem) {
            this._super();

            if (!this.isPromo) {
                return;
            }

            resolver(() => {
                this.disableComponent(elem);
            });
        },

        /**
         * @param {Object} component
         * @returns {void}
         */
        disableComponent: function (component) {
            switcher.disableComponent(component);
        },

        /**
         * @returns {Object}
         */
        getCurrentConfig: function () {
            return this.planTypes[this.currentPlanType] ?? this.planTypes.subscribe;
        },

        /**
         * @returns {string}
         */
        getBackgroundColor: function () {
            return this.getCurrentConfig()?.iconBgColor ?? '';
        },

        /**
         * @returns {string}
         */
        getIconUrl: function () {
            return this.getCurrentConfig()?.iconSrc ?? '';
        },

        /**
         * @returns {string}
         */
        getPromoText: function () {
            return this.getCurrentConfig()?.text ?? '';
        }
    });
});
