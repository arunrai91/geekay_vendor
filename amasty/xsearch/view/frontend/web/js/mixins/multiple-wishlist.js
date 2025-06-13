/**
 * multiple-wish mixin
 */

define([
    'jquery'
], function ($) {
    'use strict';

    var multipleWishlistMixin = {
        _create: function () {
            this._super();

            this._initMultipleWishlist();
            $('body').on('amsearch.popup.contentUpdated', this._initMultipleWishlist.bind(this));
        },

        /**
         * Initialize multiple wishlist for products on the page
         *
         * @private
         * @return {void}
         */
        _initMultipleWishlist: function () {
            var wishSelector = this.options.wishlistLink;

            this.options.canCreate = true;
            this.options.wishlistLink = '.amsearch-button.-wishlist';
            this._buildWishlistDropdown();
            this.options.wishlistLink = wishSelector;
        }
    };

    return function (targetWidget) {
        $.widget('mage.multipleWishlist', targetWidget, multipleWishlistMixin);

        return $.mage.multipleWishlist;
    };
});
