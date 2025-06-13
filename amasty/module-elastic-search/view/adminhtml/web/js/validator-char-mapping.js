/**
 * Elasticsearch char mapping rule validation
 */
require([
    'jquery',
    'mage/translate',
    'jquery/validate',
    'mage/validation'
], function ($) {
    'use strict';

    $.validator.addMethod(
        'amasty-validate-char-mapping',
        function (value) {
            return $.mage.isEmptyNoTrim(value) || /^\S+ ?=> ?\S+$/gm.test(value);
        },
        $.mage.__('Please enter a valid character mapping (E.g."& => and").')
    );
});
