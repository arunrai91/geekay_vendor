/**
 * Amasty Search Collapsible Widget
 */

define([
    'jquery',
    'amsearch_helpers',
    'mage/collapsible'
], function ($, helpers) {
    $.widget('amsearch.Collapsible', {
        options: {
            isMobile: $(window).width() < helpers.constants.mobile_view
        },
        selectors: {
            wrapper: '[data-amcollapse-js="wrapper"]',
            title: '[data-amcollapse-js="title"]',
            content: '[data-amcollapse-js="content"]',
            trigger: '[data-amcollapse-js="trigger"]'
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            if (!this.options.isMobile) {
                return false;
            }

            $(this.element).collapsible({
                active: true,
                openedState: '-opened',
                closedState: '-closed'
            });
        }
    });

    return $.amsearch.Collapsible;
});
