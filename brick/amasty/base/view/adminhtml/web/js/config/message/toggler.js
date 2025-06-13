define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    $.widget('ambase.messageToggler', {
        options: {
            additionalText: '',
            togglerBtn: {
                showText: $t('View More'),
                hideText: $t('Hide')
            }
        },
        textContainerSelector: '[data-ambase-message-additional]',
        toggleBtnSelector: '[data-ambase-message-toggle-btn]',
        isOpen: false,

        /**
         * @returns {void}
         */
        _create: function () {
            if (!this.options.additionalText.length) {
                return;
            }

            this._bind();
        },

        /**
         * @returns {void}
         */
        _bind: function () {
            this._on({
                [`click ${this.toggleBtnSelector}`]: this.toggle
            });
        },

        /**
         * @param {jQuery.Event} event
         */
        toggle: function (event) {
            event.preventDefault();
            this.isOpen ? this.hide() : this.show();
        },

        /**
         * @returns {void}
         */
        show: function () {
            this.getTextContainer().append(this.options.additionalText);
            this.getTogglerBtn().text(this.options.togglerBtn.hideText);
            this.isOpen = true;
        },

        /**
         * @returns {void}
         */
        hide: function () {
            this.getTextContainer().empty();
            this.getTogglerBtn().text(this.options.togglerBtn.showText);
            this.isOpen = false;
        },

        /**
         * @returns {jQuery}
         */
        getTextContainer: function () {
            return $(this.element).find(this.textContainerSelector);
        },

        /**
         * @returns {jQuery}
         */
        getTogglerBtn: function () {
            return $(this.element).find(this.toggleBtnSelector);
        }
    });

    return $.ambase.messageToggler;
});
