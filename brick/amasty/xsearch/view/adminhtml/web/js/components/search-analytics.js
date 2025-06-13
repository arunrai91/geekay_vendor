define([
    'jquery',
    'amSearchCharts'
], function ($, amSearchCharts) {
    'use strict';

    $.widget('mage.amSearchAnalytics', {
        options: {
            colors: [
                '#ffa942',
                '#5b81cc',
                '#2dca9b',
                '#79c5ca'
            ],
            dataMap: [
                'popularity',
                'product_click',
                'unique_query',
                'unique_user'
            ]
        },

        /**
         * @inheritdocs
         */
        _create: function () {
            this.chart = am4core.create(this.element[0], amSearchCharts.XYChart);
            this.chart.scrollbarX = new am4core.Scrollbar();
            this.chart.data = this.options.data;
            this.chart.cursor = new am4charts.XYCursor();
            this.chart.title = false;

            this._initCategory();
            this._initAxis();
            this._initStyles();

            this.options.dataMap.forEach(function (key) {
                this._addSeries(key);
            }.bind(this));
        },

        /**
         * Init Axis Category
         *
         * @primary
         * @return {void}
         */
        _initCategory: function () {
            var categoryAxis = this.chart.xAxes.push(new am4charts.CategoryAxis());

            categoryAxis.dataFields.category = 'created_at';
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.minGridDistance = 30;
            categoryAxis.renderer.labels.template.horizontalCenter = 'right';
            categoryAxis.renderer.labels.template.verticalCenter = 'middle';
            categoryAxis.renderer.labels.template.rotation = this.options.data.length > 10 ? -45 : 0;
            categoryAxis.renderer.minHeight = 110;
        },

        /**
         * Init Axis
         *
         * @primary
         * @return {void}
         */
        _initAxis: function () {
            var valueAxis = this.chart.yAxes.push(new am4charts.ValueAxis());

            valueAxis.renderer.minWidth = 50;
        },

        /**
         * Init Series by current name
         *
         * @primary
         * @param {String} name
         * @return {void}
         */
        _addSeries: function (name) {
            var series = this.chart.series.push(new am4charts.ColumnSeries()),
                title = name.replace(/_|-|\./g, ' ');

            title = title.charAt(0).toUpperCase() + title.slice(1);

            series.dataFields.valueY = name;

            series.cursorTooltipEnabled = false;
            series.dataFields.categoryX = 'created_at';
            series.columns.template.tooltipText = '[bold]' + title + ': {valueY}[/]';
            series.columns.template.strokeWidth = 0;
        },

        /**
         * Init Styles
         *
         * @primary
         * @return {void}
         */
        _initStyles: function () {
            var self = this;

            self.options.colors.forEach(function (color) {
                self.chart.colors.list.push(am4core.color(color));
            });

            self.chart.fontSize = 14;
            self.chart.startDuration = 0.5;

            am4core.useTheme(am4themes_animated);
        }
    });

    return $.mage.amSearchAnalytics;
});
