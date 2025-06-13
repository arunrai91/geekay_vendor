define([
    'jquery',
    'underscore',
    'uiRegistry',
    'matchMedia'
], function ($, _, registry, mediaCheck) {
    'use strict';

    $.widget('mage.amShopbyStickySidebar', {
        options: {
            offset: 0,
            minMegaMenuHeight: 60,
            filtersOverflowOffset: 20,
            mediaBreakpoint: '(min-width: 768px)'
        },
        classes: {
            sticky: '-amshopby-sticky'
        },
        selectors: {
            megaMenu: '.ammenu-main-container',
            swatchOptionsContainer: '[data-am-js="swatch-options"]',
            swatchTooltip: '.page-with-filter > .swatch-option-tooltip',
            filterTooltip: '.page-with-filter > .ui-tooltip',
            filterContent: '.block-content.filter-content',
            filterOptions: '.filter-options'
        },
        nodes: {
            window: $(window),
            body: $('body')
        },

        /**
         * @private
         * @return {void}
         */
        _create: function () {
            this._initNodes();
            this._enableSticky();
            this._swatchTooltipObserver();
            this._initFilterOverflow();

            registry.get('index = ammenu_wrapper', function (component) {
                if (component && (component.is_sticky === 1 || component.is_sticky === 3)) {
                    this._stickToMegaMenu();
                }
            }.bind(this));
        },

        /**
         * @private
         * @return {void}
         */
        _initNodes: function () {
            this.nodes.swatchOptionsContainer = this.element.find(this.selectors.swatchOptionsContainer);
            this.nodes.filterContent = this.element.find(this.selectors.filterContent);
            this.nodes.filterOptions = this.nodes.filterContent.find(this.selectors.filterOptions);
        },

        /**
         * @private
         * @return {void}
         */
        _initFilterOverflow: function () {
            var self = this,
                container = self.nodes.filterContent,
                filtersBlock = self.nodes.filterOptions,
                maxHeight;

            if (!container.length && !filtersBlock.length) {
                return;
            }

            maxHeight = container.height() - filtersBlock.height() + self.options.filtersOverflowOffset;

            mediaCheck({
                media: self.options.mediaBreakpoint,
                entry: function () {
                    filtersBlock.css('maxHeight', 'calc(100vh - ' + maxHeight + 'px)');
                },
                exit: function () {
                    filtersBlock.css('maxHeight', '');
                }
            });
        },

        /**
         * @private
         * @return {void}
         */
        _swatchTooltipObserver: function () {
            var self = this,
                swatchOptionsContainer = self.nodes.swatchOptionsContainer,
                swatchTooltip = null,
                isHovered = false;

            if (!swatchOptionsContainer.length) {
                return;
            }

            swatchOptionsContainer.hover(function () {
                isHovered = true;
            }, function () {
                isHovered = false;
            });

            self.nodes.filterOptions.on('touchstart touchmove scroll', function () {
                self._scrollCallback(isHovered, swatchTooltip);
            });

            self.nodes.window.on('touchstart touchmove scroll', function () {
                self._scrollCallback(isHovered, swatchTooltip);
            });
        },

        /**
         * @private
         * @return {void}
         */
        _scrollCallback: _.throttle(function (isHovered, swatchTooltip) {
            $(this.selectors.filterTooltip).hide();

            if (!isHovered) {
                return false;
            }

            if (!swatchTooltip) {
                // eslint-disable-next-line no-param-reassign
                swatchTooltip = $(this.selectors.swatchTooltip);
            }

            swatchTooltip.hide();

            return true;
        }, 100),

        /**
         * @private
         * @return {void}
         */
        _enableSticky: function () {
            this.element.addClass(this.classes.sticky);
            this.nodes.body.addClass(this.classes.sticky);
        },

        /**
         * @private
         * @return {void}
         */
        _stickToMegaMenu: function () {
            var megaMenuBlock = $(this.selectors.megaMenu),
                observer;

            if (!('ResizeObserver' in window)) {
                this.element.css('top', this.options.minMegaMenuHeight + this.options.offset);

                return;
            }

            observer = new ResizeObserver(this._resizeCallback.bind(this));

            if (megaMenuBlock.length) {
                observer.observe(megaMenuBlock[0]);
            }
        },

        /**
         * @private
         * @param {Array} entries
         * @return {void}
         */
        _resizeCallback: function (entries) {
            this.element.css('top', entries[0].contentRect.height + this.options.offset);
        }
    });

    return $.mage.amShopbyStickySidebar;
});
