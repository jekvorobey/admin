<template>
    <layout-main>
        <h1>{{ message }}</h1>
        <section>
            <h1>Аналитика</h1>
            <div class="row">

                <div class="col-12 col-md-6">
                    <div class="row">
                        <div class="col-12">
                            <DxButton
                                icon="refresh"
                                class="float-left mr-1"
                                @click="updateDataSourceChartOne()"
                            />
                            <DxButton
                                icon="arrowleft"
                                class="float-left mr-1"
                                @click="downDataSourceChartOne()"
                            />
                            <DxDateBox
                                ref="dateBoxOne"
                                v-model:value="dateChartOne"
                                @valueChanged="refreshDataSourceChartOne()"
                                class="float-left mr-1"
                                style="max-width: 150px"
                                type="date"
                                display-format="dd.MM.yyyy"
                            />
                            <DxButton
                                icon="arrowright"
                                class="float-left mr-1"
                                @click="upDataSourceChartOne()"
                            />
                        </div>
                        <div class="col-12">
                            <DxChart
                                ref="chartOne"
                                :data-source="dataSourceChartOne"
                                :loading-indicator=" { enabled: false } "
                            >
                                <DxSize :height="420"/>

                                <DxValueAxis
                                    :grid="{ opacity: 0.2 }"
                                    value-type="numeric"
                                >
                                    <DxLabel :customize-text="customizeChartOneLabelText"/>
                                </DxValueAxis>

                                <DxCommonSeriesSettings
                                    type="area"
                                    hover-mode="allArgumentPoints"
                                    selection-mode="allArgumentPoints"
                                    argument-field="hour"
                                >
                                    <DxLabel
                                        format="shortTime"
                                    />
                                </DxCommonSeriesSettings>

                                <DxSeries
                                    value-field="aggAmountOrdersFull"
                                    name="Всего"
                                    color="#ffa500"
                                    type="splineArea"
                                    stack="first"
                                />

                                <DxSeries
                                    value-field="aggAmountOrders"
                                    name="В работе"
                                    color="green"
                                    type="splineArea"
                                    stack="second"
                                />

                                <DxSeries
                                    value-field="aggAmountOrdersCancel"
                                    name="Отмены"
                                    color="red"
                                    type="splineArea"
                                    stack="second"
                                />

                                <DxSeries
                                    value-field="amountOrdersFull"
                                    name="Динамика"
                                    color="blue"
                                    type="splineArea"
                                    stack="second"
                                />

                                <DxSeries
                                    value-field="forecastAmountOrders"
                                    name="Прогноз"
                                    color="red"
                                    type="spline"
                                    stack="second"
                                />

                                <DxCrosshair
                                    :enabled="true"
                                    color="#000"
                                    dash-style="dot"
                                >
                                    <DxLabel
                                        :visible="true"
                                        background-color="#555"
                                    />
                                </DxCrosshair>

                                <DxTooltip
                                    :enabled="true"
                                    :format="{ precision: 0, type: 'fixedPoint' }"
                                    align="left"
                                />

                                <DxArgumentAxis :value-margins-enabled="false"/>
                                <DxLegend
                                    vertical-alignment="bottom"
                                    horizontal-alignment="center"
                                />

                                <DxExport :enabled="true"/>
                                <DxLoadingIndicator :enabled="true"/>
                            </DxChart>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="row">
                        <div class="col-12">
                            <DxButton
                                icon="refresh"
                                class="float-left mr-1"
                                @click="updateDataSourceChartTwo()"
                            />
                            <DxButton
                                icon="arrowleft"
                                class="float-left mr-1"
                                @click="downDataSourceChartTwo()"
                            />
                            <DxDateBox
                                ref="dateBoxTwo"
                                v-model:value="dateChartTwo"
                                @valueChanged="refreshDataSourceChartTwo()"
                                class="float-left mr-1"
                                style="max-width: 150px"
                                type="date"
                                display-format="monthAndYear"
                                :calendarOptions=" { maxZoomLevel: 'year', minZoomLevel: 'century' }"
                            />
                            <DxButton
                                icon="arrowright"
                                class="float-left mr-1"
                                @click="upDataSourceChartTwo()"
                            />
                        </div>
                        <div class="col-12">
                            <DxChart
                                ref="chartTwo"
                                :data-source="dataSourceChartTwo"
                                :loading-indicator=" { enabled: false } "
                            >
                                <DxSize :height="420"/>
                                <DxValueAxis
                                    :grid="{ opacity: 0.2 }"
                                    value-type="numeric"
                                >
                                    <DxLabel :customize-text="customizeChartTwoLabelText"/>
                                </DxValueAxis>

                                <DxCommonSeriesSettings
                                    type="area"
                                    hover-mode="allArgumentPoints"
                                    selection-mode="allArgumentPoints"
                                    argument-field="date"
                                >
                                    <DxLabel
                                        format="shortTime"
                                    />
                                </DxCommonSeriesSettings>

                                <DxSeries
                                    value-field="aggAmountOrdersFull"
                                    name="Всего"
                                    color="#ffa500"
                                    type="splineArea"
                                    stack="first"
                                />

                                <DxSeries
                                    value-field="aggAmountOrders"
                                    name="В работе"
                                    color="green"
                                    type="splineArea"
                                    stack="second"
                                />

                                <DxSeries
                                    value-field="aggAmountOrdersCancel"
                                    name="Отмены"
                                    color="red"
                                    type="splineArea"
                                    stack="second"
                                />

                                <DxSeries
                                    value-field="amountOrdersFull"
                                    name="Динамика"
                                    color="blue"
                                    type="splineArea"
                                    stack="second"
                                />

                                <DxSeries
                                    value-field="forecastAmountOrders"
                                    name="Прогноз"
                                    color="red"
                                    type="spline"
                                    stack="second"
                                />

                                <DxCrosshair
                                    :enabled="true"
                                    color="#000"
                                    dash-style="dot"
                                >
                                    <DxLabel
                                        :visible="true"
                                        background-color="#555"
                                    />
                                </DxCrosshair>

                                <DxTooltip
                                    :enabled="true"
                                    :format="{ precision: 0, type: 'fixedPoint' }"
                                    align="left"
                                />

                                <DxArgumentAxis :value-margins-enabled="false"/>
                                <DxLegend
                                    vertical-alignment="bottom"
                                    horizontal-alignment="center"
                                />

                                <DxExport :enabled="true"/>
                                <DxLoadingIndicator :enabled="true"/>
                            </DxChart>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <DxPivotGrid
                        ref="pivotGridThree"
                        :data-source="dataSourcePivotGridThree"
                        :allow-sorting-by-summary="true"
                        :allow-filtering="true"
                        :allow-expand-all="true"
                        :show-borders="true"
                        :show-column-grand-totals="true"
                        :show-row-grand-totals="true"
                        :show-row-totals="true"
                        :show-column-totals="false"
                        :show-totals-prior="showTotalsPrior"
                    >
                        <DxFieldChooser :enabled="true"/>
                        <DxExport :enabled="true"/>
                    </DxPivotGrid>
                </div>
            </div>
        </section>
    </layout-main>
</template>

<script>
    import moment from 'moment';

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
        DxCrosshair,
    } from 'devextreme-vue/chart';

    import {
        DxPivotGrid,
        DxFieldChooser,
    } from 'devextreme-vue/pivot-grid';

    import DxDateBox from 'devextreme-vue/date-box';
    import DxCheckBox from 'devextreme-vue/check-box';
    import DxButton from 'devextreme-vue/button';

    import PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source';
    import DataSource from 'devextreme/data/data_source';
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
            DxDateBox,
            DxCheckBox,
            DxButton,
            DxCrosshair,
        },
        created() {
            loadMessages(ruMessages);
            locale('ru');
        },
        data() {

            const dateChartOne = new Date();
            const dateChartTwo = new Date();

            const dataSourceChartOne = new DataSource({
                store: {
                    url: '/api/analytics/dashboard/sales/day-by-hour?start=' + moment(this.dateChartOne).format('YYYY-MM-DD'),
                    type: 'odata',
                    version: 4,
                    jsonp: false,
                },
                paginate: false,
            });

            const dataSourceChartTwo = new DataSource({
                store: {
                    url: '/api/analytics/dashboard/sales/month-by-day?start=' + moment(this.dateChartTwo).format('YYYY-MM-01'),
                    type: 'odata',
                    version: 4,
                    jsonp: false,
                },
                paginate: false,
            });

            const dataSourcePivotGridThree = new PivotGridDataSource({
                fields: [
                    {
                        caption: 'Тип товара',
                        width: 120,
                        dataField: 'nameType',
                        area: 'column',
                        sortBySummaryField: 'amount',
                        sortOrder: "desc",
                        displayFolder: 'Товары',
                    },
                    {
                        caption: 'Код типа',
                        width: 120,
                        dataField: 'type',
                        sortBySummaryField: 'amount',
                        sortOrder: "desc",
                        displayFolder: 'Товары',
                    },
                    {
                        caption: "Полная дата",
                        dataField: "date",
                        dataType: 'date',
                        sortOrder: "desc",
                        displayFolder: 'Дата',
                    },
                    {
                        caption: "Год",
                        dataField: "date",
                        dataType: 'date',
                        groupInterval: "year",
                        sortOrder: "desc",
                        area: "row",
                        displayFolder: 'Дата',
                    },
                    {
                        caption: "Месяц",
                        dataField: "date",
                        dataType: 'date',
                        groupInterval: "month",
                        sortOrder: "desc",
                        area: "row",
                        displayFolder: 'Дата',
                    },
                    {
                        caption: "День",
                        dataField: "date",
                        dataType: 'date',
                        groupInterval: "day",
                        sortOrder: "desc",
                        area: "row",
                        displayFolder: 'Дата',
                    },
                    {
                        caption: 'Заказы всего, шт',
                        dataField: 'countOrdersFull',
                        summaryType: 'sum',
                        format: {
                            precision: 0,
                            type: "fixedPoint",
                        },
                        customizeText: function (cellInfo) {
                            if (cellInfo.value) {
                                return cellInfo.valueText;
                            }
                        },
                        area: 'data',
                        displayFolder: 'Заказы',
                    },
                    {
                        caption: 'Заказы всего, р.',
                        dataField: 'amountOrdersFull',
                        summaryType: 'sum',
                        dataType: 'number',
                        format: {
                            precision: 0,
                            type: "fixedPoint",
                        },
                        customizeText: function (cellInfo) {
                            if (cellInfo.value) {
                                return cellInfo.valueText;
                            }
                        },
                        sortOrder: "desc",
                        displayFolder: 'Заказы',
                    },
                    {
                        caption: 'Товары всего, шт.',
                        dataField: 'countProductsFull',
                        summaryType: 'sum',
                        format: {
                            precision: 0,
                            type: "fixedPoint",
                        },
                        customizeText: function (cellInfo) {
                            if (cellInfo.value) {
                                return cellInfo.valueText;
                            }
                        },
                        area: 'data',
                        displayFolder: 'Заказы',
                    },
                    {
                        caption: 'Заказы в обр., шт',
                        dataField: 'countOrders',
                        summaryType: 'sum',
                        format: {
                            precision: 0,
                            type: "fixedPoint",
                        },
                        customizeText: function (cellInfo) {
                            if (cellInfo.value) {
                                return cellInfo.valueText;
                            }
                        },
                        area: 'data',
                        displayFolder: 'Заказы',
                    },
                    {
                        caption: 'Заказы в обр., р.',
                        dataField: 'amountOrders',
                        summaryType: 'sum',
                        dataType: 'number',
                        format: {
                            precision: 0,
                            type: "fixedPoint",
                        },
                        customizeText: function (cellInfo) {
                            if (cellInfo.value) {
                                return cellInfo.valueText;
                            }
                        },
                        sortOrder: "desc",
                        area: 'data',
                        displayFolder: 'Заказы',
                    },
                    {
                        caption: 'Товары в обр. шт',
                        dataField: 'countProducts',
                        summaryType: 'sum',
                        format: {
                            precision: 0,
                            type: "fixedPoint",
                        },
                        customizeText: function (cellInfo) {
                            if (cellInfo.value) {
                                return cellInfo.valueText;
                            }
                        },
                        area: 'data',
                        displayFolder: 'Заказы',
                    },
                    {
                        caption: "Ср.чек, р.",
                        dataType: "number",
                        format: 'fixedPoint',
                        area: "data",
                        visible: true,
                        summaryType: "custom",
                        calculateSummaryValue: function (data) {
                            if(data.value('countOrders')) {
                                return Math.round(data.value('amountOrders') / data.value('countOrders') * 100) / 100;
                            }
                        },
                        displayFolder: 'Показатели',
                    },
                    {
                        caption: "Ср.поз., р.",
                        dataType: "number",
                        format: 'fixedPoint',
                        area: "data",
                        visible: true,
                        summaryType: "custom",
                        calculateSummaryValue: function (data) {
                            if(data.value('countProducts')) {
                                return Math.round(data.value('amountOrders') / data.value('countProducts') * 100) / 100;
                            }
                        },
                        displayFolder: 'Показатели',
                    },
                    {
                        caption: "Ср.поз., шт",
                        dataType: 'number',
                        format: {
                            precision: 2,
                            type: "fixedPoint",
                        },
                        area: "data",
                        visible: true,
                        summaryType: "custom",
                        calculateSummaryValue: function (data) {
                            if(data.value('countOrders')) {
                                return Math.round(data.value('countProducts') / data.value('countOrders') * 100) / 100;
                            }
                        },
                        displayFolder: 'Показатели',
                    },
                    {
                        caption: "Отмен., шт",
                        dataField: "countOrdersCancel",
                        dataType: "number",
                        format: 'fixedPoint',
                        area: "data",
                        summaryType: "sum",
                        customizeText: function (cellInfo) {
                            if (cellInfo.value) {
                                return cellInfo.valueText;
                            }
                        },
                        displayFolder: 'Отмены',
                    },
                    {
                        caption: "Отмен., р.",
                        dataField: "amountOrdersCancel",
                        dataType: "number",
                        format: 'fixedPoint',
                        area: "data",
                        summaryType: "sum",
                        customizeText: function (cellInfo) {
                            if (cellInfo.value) {
                                return cellInfo.valueText;
                            }
                        },
                        displayFolder: 'Отмены',
                    },
                    {
                        caption: "Отмен. тов, шт",
                        dataField: "countProductsCancel",
                        dataType: "number",
                        format: 'fixedPoint',
                        summaryType: "sum",
                        customizeText: function (cellInfo) {
                            if (cellInfo.value) {
                                return cellInfo.valueText;
                            }
                        },
                        displayFolder: 'Отмены',
                    },
                    {
                        caption: "Отмен., %",
                        dataType: "number",
                        format: {
                            precision: 1,
                            type: "percent",
                        },
                        area: "data",
                        summaryType: "custom",
                        calculateSummaryValue: function (data) {
                            if(data.value('countOrdersCancel')) {
                                return Math.round((data.value('countOrdersCancel') / (data.value('countOrders') + data.value('countOrdersCancel'))) * 10000)/10000;
                            }
                        },
                        displayFolder: 'Отмены',
                    },
                    {
                        caption: 'ТО 2, %',
                        dataField: 'amountOrders',
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
                        displayFolder: 'Показатели',
                    },
                    {
                        caption: 'ТО 1, %',
                        dataField: 'amountOrders',
                        summaryType: 'sum',
                        dataType: 'number',
                        area: 'data',
                        format: {
                            precision: 1,
                            type: "percent",
                        },
                        summaryDisplayMode: 'percentOfColumnTotal',
                        displayFolder: 'Показатели',
                    }
                ],
                store: {
                    url: '/api/analytics/dashboard/sales/all-period-by-day',
                    type: 'odata',
                    version: 4,
                    jsonp: true,
                }
            });

            return {
                dataSourceChartOne,
                dataSourceChartTwo,
                dataSourcePivotGridThree,
                dateChartOne: dateChartOne,
                dateChartTwo: dateChartTwo,
                showTotalsPrior: "columns",
            };
        },
        methods: {
            customizeChartOneLabelText({ valueText }) {
                return `${valueText}`;
            },

            customizeChartTwoLabelText({ valueText }) {
                return `${valueText}`;
            },

            updateDataSourceChartOne() {
                this.dataSourceChartOne.reload();
            },

            downDataSourceChartOne() {
                this.dateChartOne.setDate(this.dateChartOne.getDate() - 1);
                this.refreshDataSourceChartOne();
            },

            upDataSourceChartOne() {
                this.dateChartOne.setDate(this.dateChartOne.getDate() + 1);
                this.refreshDataSourceChartOne();
            },

            refreshDataSourceChartOne() {
                this.$refs.dateBoxOne.instance.value = this.dateChartOne;
                this.dataSourceChartOne = new DataSource({
                    store: {
                        url: '/api/analytics/dashboard/sales/day-by-hour?start=' + moment(this.dateChartOne).format('YYYY-MM-DD'),
                        type: 'odata',
                        version: 4,
                        jsonp: false,
                    },
                    paginate: false,
                });
            },

            updateDataSourceChartTwo() {
                this.dataSourceChartTwo.reload();
            },

            downDataSourceChartTwo() {
                this.dateChartTwo.setMonth(this.dateChartTwo.getMonth() - 1);
                this.refreshDataSourceChartTwo();
            },

            upDataSourceChartTwo() {
                this.dateChartTwo.setMonth(this.dateChartTwo.getMonth() + 1);
                this.refreshDataSourceChartTwo();
            },

            refreshDataSourceChartTwo() {
                this.$refs.dateBoxTwo.instance.value = this.dateChartTwo;
                this.dataSourceChartTwo = new DataSource({
                    store: {
                        url: '/api/analytics/dashboard/sales/month-by-day?start=' + moment(this.dateChartTwo).format('YYYY-MM-01'),
                        type: 'odata',
                        version: 4,
                        jsonp: false,
                    },
                    paginate: false,
                });
            },
        },
    };
</script>
