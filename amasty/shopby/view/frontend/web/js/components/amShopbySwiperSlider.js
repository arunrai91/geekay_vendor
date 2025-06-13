/**
 * Swiper Slider Init
 */

define([
    'swiper',
    'domReady!'
], function (Swiper) {
    'use strict';

    return function (config, element) {
        new Swiper(element, config);
    };
});
