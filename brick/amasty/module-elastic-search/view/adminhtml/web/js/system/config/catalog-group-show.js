define([
    'jquery'
], function ($) {
    'use strict';

    return function (options) {
        const catalogGroup = $('#amasty_elastic_catalog-head');

        if (options.isEngineValid) {
            catalogGroup.show();
        } else {
            catalogGroup.hide();
        }
    }
});
