define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Storage for clicked products links inside popup
     * Used for proper breadcrumbs display
     */
    return {
        /**
         * @typedef {Object} LinkData
         * @property {string} hash - Url hash (using btoa)
         * @property {number} timestamp - Timestamp when the link was clicked
         */

        options: {
            validLinkTimeout: 10 * 1000, // 10 seconds
            storageKey: 'amsearch-product-links',
            productsUrlSelector: 'a.amsearch-image, a.amsearch-link, a.action.view'
        },

        /**
         * @param {Object} popupContainer
         * @return {void}
         */
        bindLinks: function (popupContainer) {
            $(popupContainer).find(this.options.productsUrlSelector)
                .each(function (index, link) {
                    $(link).on('click', this.clickEvent.bind(this));
                }.bind(this));
        },

        /**
         * @param {Object} popupContainer
         * @return {void}
         */
        saveLinks: function (popupContainer) {
            $(popupContainer).find(this.options.productsUrlSelector)
                .each(function (index, link) {
                    this._saveLink(link);
                }.bind(this));
        },

        /**
         * @param {Object} event
         * @return {void}
         */
        clickEvent: function (event) {
            const href = $(event.delegateTarget).attr('href');
            if (!href) {
                return;
            }

            event.preventDefault();
            this._saveLink(href);
            document.location.href = href;
        },

        /**
         * @param {string} link
         * @return {void}
         */
        _saveLink: function (link) {
            let links = this.getLinks();
            links.push({
                hash: btoa(link),
                timestamp: $.now()
            });
            this.setLinks(links);
        },

        /**
         * @return {boolean}
         */
        hasCurrentLink: function () {
            this.clearOutdated();

            return this.getLinks().some(function (storageLink) {
                return atob(storageLink.hash) === document.location.href;
            });
        },

        /**
         * @return {[LinkData]}
         */
        getLinks: function () {
            return JSON.parse(localStorage.getItem(this.options.storageKey)) || [];
        },

        /**
         * @param {[LinkData]} links
         */
        setLinks: function (links) {
            localStorage.setItem(this.options.storageKey, JSON.stringify(links));
        },

        /**
         * Clear storage from links that was clicked later than validLinkTimeout
         *
         * @return {void}
         */
        clearOutdated: function () {
            let links = this.getLinks();

            links = links.filter(function (link) {
                return ($.now() - link.timestamp) <= this.options.validLinkTimeout;
            }.bind(this));

            this.setLinks(links);
        }
    };
});

