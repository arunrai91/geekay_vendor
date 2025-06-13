define([
    'Magento_Ui/js/form/components/fieldset',
    'underscore',
    'uiRegistry'
], function (Component, _, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            rulesActions: [
                // SP Rules
                'buyxgety_anyproducts',
                'thecheapest',
                'thecheapest_fixprice',
                'themostexpencive',
                'moneyamount',
                'eachn_perc',
                'eachn_fixdisc',
                'eachn_fixprice',
                'eachmaftn_perc',
                'eachmaftn_fixdisc',
                'eachmaftn_fixprice',
                'groupn',
                'groupn_disc',
                'buyxgety_perc',
                'buyxgety_fixprice',
                'buyxgety_fixdisc',
                'buyxgetn_perc',
                'buyxgetn_fixprice',
                'buyxgetn_fixdisc',
                'aftern_fixprice',
                'aftern_disc',
                'aftern_fixdisc',
                'setof_percent',
                'setof_fixed',
                'tiered_wholecheaper',
                'tiered_buyxgetcheapern',
                'tiered_discount_percent',
                // Free Gift Rules
                'ampromo_product',
                'ampromo_items',
                'ampromo_cart',
                'ampromo_spent',
                'ampromo_eachn'
            ],
            listens: {
                '${ $.parentName }.actions.simple_action:value': 'onChange'
            }
        },

        /**
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();
            registry.get(this.parentName + '.actions.simple_action', function (component) {
                this.setVisibility(component.value());
            }.bind(this));

            return this;
        },

        /**
         * @param {String} value
         * @returns {void}
         */
        onChange:function (value) {
            this.setVisibility(value);
        },

        /**
         * @param {String} value
         * @returns {void}
         */
        setVisibility: function (value) {
            this.visible(_.contains(this.rulesActions, value));
        }
    });
});
