/**
 * Amasty init mage tooltip widget
 */

define([
    'jquery',
    'mage/tooltip'
], function ($) {
    'use strict';

    $.widget('am.brandsTooltipInit', {
        options: {
            additionalClasses: '',
            position: {
                my: null,
                at: null,
                collision: null
            },
            selector: null
        },
        classes: {
            tooltip: 'amshopby-brand-tooltip',
            arrow: 'arrow'
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            var self = this,
                current;

            $(this.element).tooltip({
                position: {
                    my: self.options.position.my || 'left bottom',
                    at: self.options.position.at || 'right top',
                    collision: self.options.position.collision || 'flip flip',
                    using: function (position, feedback) {
                        $(this).css(position);

                        $('<div>')
                            .addClass(self.classes.arrow)
                            .addClass(feedback.vertical)
                            .addClass(feedback.horizontal)
                            .appendTo(this);
                    }
                },
                tooltipClass: self.classes.tooltip + ' ' + self.options.additionalClasses,
                content: function () {
                    current = $(this).is(self.options.selector) ? $(this) : $(this).parent();

                    return current.data('tooltip-content');
                }
            });
        }
    });

    return $.am.brandsTooltipInit;
});
