/**
 * Slick Slider Initialization
 *
 * @desc Slick slider init and generating options
 */

define([
    'jquery',
    'Amasty_Base/vendor/slick/slick.min'
], function ($) {
    $.widget('amsearch.ProductSlider', {
        defaultSliderOptions: {
            slidesToShow: 3,
            slidesToScroll: 3,
            infinite: false,
            dots: true,
            arrows: true,
            responsive: [
                {
                    breakpoint: 1280,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                },
                {
                    breakpoint: 780,
                    settings: {
                        arrows: false,
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                }
            ]
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            var options = _.extend(this.defaultSliderOptions, this.options.sliderOptions);

            if (this.options.productsCount <= 3) {
                return false;
            }

            if (this.options.observer) {
                this._initObserver(this.options.observer);
            }

            $(this.element).slick(options);
        },

        /**
         * Slick Slider Position checking via subscriber
         *
         * @desc checking and fixing new slick sliders positions
         * @param {Object} observer - ko observer
         * @return {void}
         */
        _initObserver: function (observer) {
            var $slider = $(this.element);

            observer.subscribe(function (value) {
                if (value) {
                    $slider.slick('setPosition');
                    $slider.slick('refresh');
                    $slider.slick('setDimensions');
                }
            }.bind(this));
        }
    });

    return $.amsearch.ProductSlider;
});
