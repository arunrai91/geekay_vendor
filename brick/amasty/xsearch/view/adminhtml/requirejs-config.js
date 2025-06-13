var config = {
    map: {
        '*': {
            amSearchAnalytics: 'Amasty_Xsearch/js/components/search-analytics',
            amSearchCharts: 'Amasty_Xsearch/js/lib/amcharts4/charts.min'
        }
    },
    shim: {
        'Amasty_Xsearch/js/lib/amcharts4/core.min': {
            init: function () {
                return window.am4core;
            }
        },
        'Amasty_Xsearch/js/lib/amcharts4/charts.min': {
            deps: [
                'Amasty_Xsearch/js/lib/amcharts4/core.min',
                'Amasty_Xsearch/js/lib/amcharts4/animated.min'
            ],
            exports: 'Amasty_Xsearch/js/lib/amcharts4/charts.min',
            init: function () {
                return window.am4charts;
            }
        },
        'Amasty_Xsearch/js/lib/amcharts4/animated.min': {
            deps: ['Amasty_Xsearch/js/lib/amcharts4/core.min'],
            exports: 'Amasty_Xsearch/js/lib/amcharts4/animated.min',
            init: function () {
                return window.am4themes_animated;
            }
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Amasty_Xsearch/js/validator-hex-code': true
            }
        }
    }
};
