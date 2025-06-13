define([
    'Amasty_Base/js/form/element/ui-promotion-select'
],function (UiSelect) {
    'use strict';

    return UiSelect.extend({
        /**
         * @returns {Object}
         */
        initialize: function () {
            this._super();

            if (this.default && !this.value().length) {
                const defaultOption = this.options().find(option => option.value === this.default);

                !!defaultOption && this.value(defaultOption.value);
            }

            return this;
        }
    });
});
