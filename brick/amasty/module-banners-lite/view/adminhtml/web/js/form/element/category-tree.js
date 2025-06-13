define([
    'underscore',
    'uiRegistry',
    'Magento_Catalog/js/components/new-category',
    'jquery'
], function (_, uiRegistry, Category, $) {
    'use strict';

    return Category.extend({
        /**
         * @param {Object} data
         * @returns {Object} Chainable.
         */
        toggleOptionSelected: function (data) {
            this._super(data);

            if (this.isSelected(data.value) && data?.[this.separator]) {
                // Show and select all nested category
                this.openChildByData(data);
                _.each(data[this.separator], function (child) {
                    this.selectChilds(child);
                }.bind(this));
            }

            return this;
        },

        /**
         * @param {Object} data
         * @returns {void}
         */
        selectChilds: function (data) {
            if (!this.isSelected(data.value)) {
                this.value.push(data.value);
            }
            if (data?.[this.separator]) {
                this.openChildByData(data);
                _.each(data[this.separator], function (child) {
                    this.selectChilds(child);
                }.bind(this));
            }
        },

        /**
         * @param {Object} data
         * @returns {void}
         */
        openChildByData: function (data) {
            const contextElement = $(this.cacheUiSelect).find('li')[this.getOptionIndex(data)];
            $(contextElement).children('ul').show();
        }
    });
});
