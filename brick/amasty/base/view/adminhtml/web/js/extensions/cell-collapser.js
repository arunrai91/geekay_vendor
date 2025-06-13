define([
    'jquery',
    'mage/translate',
    'mage/template'
], function ($, $t, templateRenderer) {
    $.widget('ambase.cellCollapser', {
        options: {
            togglerBtn: {
                showText: $t('View More'),
                hideText: $t('Hide'),
                className: 'ambase-toggler-btn',
                template: '<button type="button" class="<%- className %>"><%- buttonText %></button>'
            },
            contentMaxHeight: '11rem',
            activeClassName: '-active',
            withCollapserClassName: '-with-collapser'
        },
        selectors: {
            collapsibleContent: '.ambase-collapsible-content'
        },
        isOpen: false,

        /**
         * @returns {void}
         */
        _create: function () {
            if (!this.hasOverflowContent()) {
                return;
            }

            this._addClass(this.options.withCollapserClassName);
            this.createTogglerButton();
            this._bind();
        },

        /**
         *
         * @returns {Boolean}
         */
        hasOverflowContent: function () {
            const collapsibleContent = this.getCollapsibleContent();

            return collapsibleContent.prop('clientHeight') < collapsibleContent.prop('scrollHeight');
        },

        /**
         * @returns {void}
         */
        createTogglerButton: function () {
            $(templateRenderer(
                this.options.togglerBtn.template,
                {
                    buttonText: this.options.togglerBtn.showText,
                    className: this.options.togglerBtn.className
                }
            )).insertAfter(this.getCollapsibleContent())
        },

        /**
         * @returns {void}
         */
        _bind: function () {
            this._on({
                [`click .${this.options.togglerBtn.className}`]: this.toggle
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
            this.getTogglerBtn().text(this.options.togglerBtn.hideText);
            this._addClass(this.options.activeClassName);
            this.getCollapsibleContent().animate(
                { maxHeight: this.getCollapsibleContent().prop('scrollHeight') },
                150
            );
            this.isOpen = true;
        },

        /**
         * @returns {void}
         */
        hide: function () {
            this.getTogglerBtn().text(this.options.togglerBtn.showText);
            this._removeClass(this.options.activeClassName);
            this.getCollapsibleContent().animate(
                { maxHeight: this.options.contentMaxHeight },
                150
            );
            this.isOpen = false;
        },

        /**
         * @returns {jQuery}
         */
        getTogglerBtn: function () {
            return $(this.element).find('.' + this.options.togglerBtn.className);
        },

        /**
         * @returns {jQuery}
         */
        getCollapsibleContent: function () {
            return $(this.element).find(this.selectors.collapsibleContent);
        }
    });

    return $.ambase.cellCollapser;
});
