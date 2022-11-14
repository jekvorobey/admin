<template>
    <layout-main>
        <h1>{{ message }}</h1>
        <section>
            <h1>Аналитика</h1>

            <div class="options">
                <div class="caption">Options</div>
                <div class="option">
                    <DxCheckBox
                        id="show-totals-prior"
                        :value="false"
                        :on-value-changed="onShowTotalsPriorChanged"
                        text="Show Totals Prior"
                    />
                </div>
                <div class="option">
                    <DxCheckBox
                        id="data-field-area"
                        :value="false"
                        :on-value-changed="onDataFieldAreaChanged"
                        text="Data Field Headers in Rows"
                    />
                </div>
                <div class="option">
                    <DxCheckBox
                        id="row-header-layout"
                        :value="true"
                        :on-value-changed="onRowHeaderLayoutChanged"
                        text="Tree Row Header Layout"
                    />
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6">
                    <DxChart
                        ref="chartOne"
                        :data-source="dataSourceChartOne"
                    >
                        <DxSize :height="420"/>
                        <DxValueAxis
                            :grid="{ opacity: 0.2 }"
                            value-type="numeric"
                        >
                            <DxLabel :customize-text="customizeChartOneLabelText"/>
                        </DxValueAxis>
                        <DxArgumentAxis type="discrete">
                            <DxGrid
                                :visible="true"
                                :opacity="0.5"
                            />
                        </DxArgumentAxis>
                        <DxCommonPaneSettings>
                            <DxBorder
                                :visible="true"
                                :width="2"
                                :top="false"
                                :right="false"
                            />
                        </DxCommonPaneSettings>
                        <DxSeries
                            argument-field="Number"
                            value-field="Temperature"
                            type="spline"
                        />
                        <DxLegend :visible="false"/>
                        <DxTooltip
                            :enabled="true"
                            :customize-tooltip="customizeChartOneTooltip"
                        />
                        <DxExport :enabled="true"/>
                        <DxLoadingIndicator :enabled="true"/>
                    </DxChart>
                </div>
                <div class="col-12 col-md-6">
                    <DxChart
                        ref="chartTwo"
                        :data-source="dataSourceChartTwo"
                    >
                        <DxSize :height="420"/>
                        <DxValueAxis
                            :grid="{ opacity: 0.2 }"
                            value-type="numeric"
                        >
                            <DxLabel :customize-text="customizeChartTwoLabelText"/>
                        </DxValueAxis>
                        <DxArgumentAxis type="discrete">
                            <DxGrid
                                :visible="true"
                                :opacity="0.5"
                            />
                        </DxArgumentAxis>
                        <DxCommonPaneSettings>
                            <DxBorder
                                :visible="true"
                                :width="2"
                                :top="false"
                                :right="false"
                            />
                        </DxCommonPaneSettings>
                        <DxSeries
                            argument-field="Number"
                            value-field="Temperature"
                            type="spline"
                        />
                        <DxLegend :visible="false"/>
                        <DxTooltip
                            :enabled="true"
                            :customize-tooltip="customizeChartTwoTooltip"
                        />
                        <DxExport :enabled="true"/>
                        <DxLoadingIndicator :enabled="true"/>
                    </DxChart>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <!--
                    <DxChart ref="chartThree">
                        <DxArgumentAxis type="discrete">
                            <DxGrid
                                :visible="true"
                                :opacity="0.5"
                            />
                        </DxArgumentAxis>
                        <DxTooltip
                            :enabled="true"
                            :customize-tooltip="customizeChartThreeTooltip"
                        />
                        <DxAdaptiveLayout :width="450"/>
                        <DxSize :height="200"/>
                        <DxCommonSeriesSettings type="bar"/>
                    </DxChart>
                    -->
                    <DxPivotGrid
                        ref="pivotGridThree"
                        :data-source="dataSourcePivotGridThree"
                        :allow-sorting-by-summary="true"
                        :allow-filtering="true"
                        :show-borders="true"
                        :show-column-grand-totals="true"
                        :show-row-grand-totals="true"
                        :show-row-totals="true"
                        :show-column-totals="false"
                    >
                        <DxFieldChooser
                            :enabled="true"
                            :height="600"
                        />
                    </DxPivotGrid>
                </div>
            </div>
        </section>
    </layout-main>
</template>

<script>

    import DxChart, {
        DxArgumentAxis,
        DxSeries,
        DxLegend,
        DxAdaptiveLayout,
        DxCommonSeriesSettings,
        DxSize,
        DxTooltip,
        DxValueAxis,
        DxCommonPaneSettings,
        DxGrid,
        DxBorder,
        DxLabel,
        DxExport,
        DxLoadingIndicator,
    } from 'devextreme-vue/chart';

    import {
        DxPivotGrid,
        DxFieldChooser,
    } from 'devextreme-vue/pivot-grid';

    import PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source';
    import DataSource from 'devextreme/data/data_source';
    import DxCheckBox from 'devextreme-vue/check-box';
    import 'devextreme/data/odata/store';

    import ruMessages from "devextreme/localization/messages/ru.json";
    import { locale, loadMessages } from "devextreme/localization";

    const currencyFormatter = new Intl.NumberFormat('ru-Ru', {
        style: 'currency',
        currency: 'Rub',
        minimumFractionDigits: 0,
    });

    export default {
        name: 'page-index',
        props: [
            'message'
        ],
        components: {
            DxChart,
            DxArgumentAxis,
            DxSeries,
            DxLegend,
            DxAdaptiveLayout,
            DxCommonSeriesSettings,
            DxSize,
            DxTooltip,
            DxPivotGrid,
            DxFieldChooser,
            DxValueAxis,
            DxCommonPaneSettings,
            DxGrid,
            DxBorder,
            DxLabel,
            DxExport,
            DxLoadingIndicator,
        },
        created() {
            loadMessages(ruMessages);
            locale('ru');
        },
        data() {

            const dataSourceChartOne = new DataSource({
                store: {
                    url: 'https://js.devexpress.com/Demos/WidgetsGallery/odata/WeatherItems',
                    type: 'odata',
                    version: 4,
                    //jsonp: true,
                },
                postProcess(results) {
                    return results[0].DayItems;
                },
                expand: 'DayItems',
                filter: ['Id', '=', 1],
                paginate: false,
            });

            const dataSourceChartTwo = new DataSource({
                store: {
                    url: 'https://js.devexpress.com/Demos/WidgetsGallery/odata/WeatherItems',
                    type: 'odata',
                    version: 4,
                    //jsonp: true,
                },
                postProcess(results) {
                    return results[0].DayItems;
                },
                expand: 'DayItems',
                filter: ['Id', '=', 1],
                paginate: false,
            });

            const dataSourcePivotGridThree = new PivotGridDataSource({
                fields: [
                    {
                        caption: 'Тип',
                        width: 120,
                        dataField: 'region',
                        area: 'column',
                        sortBySummaryField: 'amount',
                        sortOrder: "desc"
                    },
                    {
                        caption: "Год",
                        dataField: "date",
                        dataType: 'date',
                        groupInterval: "year",
                        sortOrder: "desc",
                        area: "row",
                    },
                    {
                        caption: "Месяц",
                        dataField: "date",
                        dataType: 'date',
                        groupInterval: "month",
                        sortOrder: "desc",
                        area: "row",
                    },
                    {
                        caption: "День",
                        dataField: "date",
                        dataType: 'date',
                        groupInterval: "day",
                        sortOrder: "desc",
                        area: "row",
                    },
                    {
                        caption: 'Кол-во',
                        summaryType: 'count',
                        format: {
                            precision: 0,
                            type: "fixedPoint",
                        },
                        area: 'data',
                    },
                    {
                        caption: 'Всего',
                        dataField: 'amount',
                        summaryType: 'sum',
                        dataType: 'number',
                        format: {
                            precision: 0,
                            type: "fixedPoint",
                        },
                        sortOrder: "desc",
                        area: 'data',
                    },
                    {
                        caption: '%',
                        dataField: 'amount',
                        summaryType: 'sum',
                        dataType: 'number',
                        area: 'data',
                        format: {
                            precision: 1,
                            type: "percent",
                        },
                        //runningTotal: 'column',
                        //allowCrossGroupCalculation: true,
                        summaryDisplayMode: 'percentOfRowGrandTotal',
                        showGrandTotals: false,
                    },
                    {
                        caption: '%',
                        dataField: 'amount',
                        summaryType: 'sum',
                        dataType: 'number',
                        area: 'data',
                        format: {
                            precision: 1,
                            type: "percent",
                        },
                        summaryDisplayMode: 'percentOfColumnTotal',
                    }
                ],
                store: {
                    url: '/analytics/dashboard/saleAllPeriodByMonth',
                    type: 'odata',
                    version: 4,
                    jsonp: true,
                }
            });

            return {
                dataSourceChartOne,
                dataSourceChartTwo,
                dataSourcePivotGridThree,
            };
        },
        methods: {
            onValueChanged({ value }) {
                this.dataSourceChartTwo.filter(['Id', '=', value]);
                this.dataSourceChartTwo.load();
            },

            customizeChartOneLabelText({ valueText }) {
                //return `${valueText}${'&#176C'}`;
                return `${valueText}`;
            },

            customizeChartOneTooltip({ valueText }) {
                return {
                    //text: `${valueText}${'&#176C'}`,
                    text: `${valueText}`,
                };
            },

            customizeChartTwoLabelText({ valueText }) {
                //return `${valueText}${'&#176C'}`;
                return `${valueText}`;
            },

            customizeChartTwoTooltip({ valueText }) {
                return {
                    //text: `${valueText}${'&#176C'}`,
                    text: `${valueText}`,
                };
            },

            customizeChartThreeTooltip(args) {
                const valueText = currencyFormatter.format(args.originalValue);
                return {
                    html: `${args.seriesName} | Всего<div class='currency'>${valueText}</div>`,
                };
            },
        },
        /*
        mounted() {
            const pivotGridThree = this.$refs.pivotGridThree.instance;
            const chartThree = this.$refs.chartThree.instance;
            pivotGridThree.bindChart(chartThree, {
                dataFieldsDisplayMode: 'splitPanes',
                alternateDataFields: true,
            });
        },
        */
    };
</script>

<style>
    .currency {
        text-align: center;
    }
</style>