define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.amBaseCopyToClipboardButton', {
        options: {
            targetInputSelector: null,
            disableTimeout: 1500
        },

        /**
         * @private
         * @returns {void}
         */
        _create: function () {
            if ($(this.options.targetInputSelector).length === 0) {
                return;
            }

            this._bind();
        },

        /**
         * @private
         * @returns {void}
         */
        _bind: function () {
            this._on({
                'click': this.clickHandler
            });
        },

        /**
         * @returns {void}
         */
        clickHandler: async function () {
            await this.copy();
            this.toggleDisablingByTimeout();
        },

        /**
         * @returns {void}
         */
        copy: async function () {
            try {
                await navigator.clipboard.writeText($(this.options.targetInputSelector).val());
            } catch (error) {
                // Try to fallback with old method
                $(this.options.targetInputSelector).select();
                document.execCommand("copy");
            }
        },

        /**
         * @returns {void}
         */
        toggleDisablingByTimeout: function () {
            $(this.element).prop('disabled', true);

            setTimeout(() => {
                $(this.element).prop('disabled', false);
            }, this.options.disableTimeout);
        }
    });

    return $.mage.amBaseCopyToClipboardButton;
});
