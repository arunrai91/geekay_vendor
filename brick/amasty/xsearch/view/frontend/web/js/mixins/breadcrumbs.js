define([
    'jquery',
    'amsearchProductLinksStorage'
], function ($, linksStorage) {
    'use strict';

    let breadcrumbsMixin = {
        _resolveCategoryUrl: function () {
            linksStorage.clearOutdated();

            if (linksStorage.hasCurrentLink()) {
                let categoryUrl = document.location.href;

                if (categoryUrl.indexOf('?') > 0) {
                    categoryUrl = categoryUrl.substring(0, categoryUrl.indexOf('?'));
                }

                return categoryUrl;
            }

            return this._super();
        }
    };

    return function (targetWidget) {
        $.widget('mage.breadcrumbs', targetWidget, breadcrumbsMixin);

        return $.mage.breadcrumbs;
    };
});
