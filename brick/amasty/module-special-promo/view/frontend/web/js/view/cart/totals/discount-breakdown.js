define([
    'Magento_SalesRule/js/view/summary/discount',
    'jquery',
    'Magento_Checkout/js/model/quote'
], function (Component, $, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Rules/summary/discount-breakdown',
            rules: [],
            isExpanded: false,
            cartSelector: '.cart-summary tr.totals',
            checkoutSelector: '.totals.discount',
            discountTitleSelector: '.totals:has(.discount) .title',
            totalRulesSelector: '.total-rules',
            expandBreakpoint: '(min-width: 641px)'
        },
        keycodes: {
            toggle: [13, 32], // enter and space
        },

        initObservable: function () {
            this._super();
            this.observe({
                rules: [],
                isExpanded: window.matchMedia(this.expandBreakpoint).matches,
            });

            return this;
        },

        /**
         * initialize
         */
        initialize: function () {
            this._super();
            this.rules(this.getRules());
            quote.totals.subscribe(this.getDiscountDataFromTotals.bind(this));
            this.isExpanded.subscribe(this.expandChangeHandler.bind(this));
            this.initToggleBreakdown();
        },

        /**
         * getRules
         */
        getRules: function () {
            return this.amount.length ? this.amount : [];
        },

        /**
         * @override
         *
         * @returns {Boolean}
         */
        isDisplayed: function () {
            return this.getPureValue() != 0;
        },

        /**
         * @param {Boolean} isExpanded
         * @returns {void}
         */
        expandChangeHandler: function (isExpanded) {
            isExpanded ? this.openBreakdown() : this.closeBreakdown();
        },

        /**
         * @override
         *
         * @returns {Boolean}
         */
        initToggleBreakdown: function () {
            $(document).on('click', this.checkoutSelector, this.toggleExpand.bind(this));
            $(document).on('click', this.cartSelector, this.toggleExpand.bind(this));
        },

        /**
         * @returns {void}
         */
        toggleExpand: function () {
            this.isExpanded(!this.isExpanded());
        },

        /**
         * @returns {void}
         */
        openBreakdown: function () {
            $(this.totalRulesSelector).show();
            $(this.discountTitleSelector).removeClass('-collapsed');
            $(this.discountTitleSelector).attr('aria-expanded', true);
        },

        /**
         * @returns {void}
         */
        closeBreakdown: function () {
            $(this.totalRulesSelector).hide();
            $(this.discountTitleSelector).addClass('-collapsed');
            $(this.discountTitleSelector).attr('aria-expanded', false);
        },

        /**
         * @returns {void}
         */
        setInitialState: function () {
            this.expandChangeHandler(this.isExpanded());
            this.setWcagProps();
            this.setWcagHandlers();
            $(this.discountTitleSelector).addClass('-enabled');
        },

        /**
         * @returns {void}
         */
        setWcagProps: function () {
            const $discountTitle = $(this.discountTitleSelector);

            $discountTitle.attr('role', 'button');
            $discountTitle.attr('tabindex', '0');
            $discountTitle.attr('aria-expanded', true);
        },

        /**
         * @returns {void}
         */
        setWcagHandlers: function () {
            $(this.discountTitleSelector)
                .off('keypress')
                .on('keypress', (event) => {
                    if (this.keycodes.toggle.includes(event.keyCode)) {
                        event.preventDefault();
                        this.toggleExpand();
                    }
                });
        },

        /**
         * @param {Array} totals
         */
        getDiscountDataFromTotals: function (totals) {
            if (totals.extension_attributes && totals.extension_attributes.amrule_discount_breakdown) {
                this.rules(totals.extension_attributes.amrule_discount_breakdown);
            } else {
                this.rules(null);
            }
        }
    });
});
