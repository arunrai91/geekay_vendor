define([
    'Magento_Ui/js/grid/toolbar',
    'underscore',
    'jquery'
], function (toolbar, _, $) {
    'use strict';

    return toolbar.extend({
        defaults: {
            pageMainActionsSelector: '.page-main-actions:not(.modal-slide .page-main-actions)'
        },

        /**
         * Shows sticky toolbar.
         *
         * @returns {Object} Chainable.
         */
        show: function () {
            if ($(this.pageMainActionsSelector).length === 0) {
                this.$sticky.style.top = 0;
            }

            return this._super();
        },

        /**
         * Checks if sticky toolbar covers original elements.
         *
         * @returns {Boolean}
         */
        isCovered: function () {
            var pageMainActionsIndent = 77,
                stickyTop;

            if ($(this.pageMainActionsSelector).length > 0) {
                pageMainActionsIndent = 0;
            }

            stickyTop = this._stickyTableTop - pageMainActionsIndent + this._wScrollTop;

            return stickyTop > this._tableTop;
        }
    });
});
