<template>
    <layout-main>
        <DxDataGrid
            ref="dataGrid"
            :data-source="dataSource"
            :remote-operations="true"
            :allow-column-reordering="true"
            :allow-column-resizing="true"
            :column-resizing-mode="columnResizingMode"
            :column-auto-width="true"
            :row-alternation-enabled="true"
            :show-borders="true"
            :height="gridHeight"
            :width="gridWidth"

            @row-dbl-click="onRowDblClick"
        >
            <DxColumn
                data-field="orderId"
                data-type="number"
                :width="70"

            />
            <DxColumn
                data-field="orderType"
                data-type="number"
                :width="50"
            />
            <DxColumn
                data-field="orderTypeName"
                data-type="string"
                :width="100"
            />
            <DxColumn
                data-field="paymentType"
                data-type="string"
                :width="100"
            />
            <DxColumn
                data-field="orderStatus"
                data-type="number"
                :width="50"
            />
            <DxColumn
                data-field="orderStatusName"
                data-type="string"
                :width="100"
            />
            <DxColumn
                data-field="paymentStatus"
                data-type="number"
                :width="50"
            />
            <DxColumn
                data-field="paymentStatusName"
                data-type="string"
                :width="100"
            />
            <DxColumn
                data-field="createdAt"
                data-type="date"
                :width="100"
            />
            <DxColumn
                data-field="payedAt"
                data-type="date"
                :width="100"
            />
            <DxColumn
                data-field="brandId"
                data-type="number"
                :width="50"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="brandName"
                data-type="string"
                :width="150"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="categoryGroupId"
                data-type="number"
                :width="50"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="categoryGroupName"
                data-type="string"
                :width="150"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="categoryId"
                data-type="number"
                :width="50"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="categoryName"
                data-type="string"
                :width="150"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="merchantId"
                data-type="number"
                :width="50"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="merchantName"
                data-type="string"
                :width="150"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="offerId"
                data-type="number"
                :width="100"
            />
            <DxColumn
                data-field="productId"
                data-type="number"
                :width="100"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="vendorCode"
                data-type="string"
                :width="150"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="productName"
                data-type="string"
                :width="400"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="optionNames"
                data-type="string"
                :width="150"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="optionValues"
                data-type="string"
                :width="150"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="qty"
                data-type="number"
                :width="50"
            />
            <DxColumn
                data-field="cost"
                data-type="number"
                :width="80"
            />
            <DxColumn
                data-field="price"
                data-type="number"
                :width="80"
            />
            <DxColumn
                data-field="shipmentsNumber"
                data-type="string"
                :width="150"
            />
            <DxColumn
                data-field="deliveryAt"
                data-type="date"
                :width="100"
            />
            <DxColumn
                data-field="deliveredAt"
                data-type="date"
                :width="100"
            />
            <DxColumn
                data-field="overdueDays"
                data-type="number"
                :width="50"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="pointId"
                data-type="number"
                :width="100"
            />
            <DxColumn
                data-field="cityName"
                data-type="string"
                :width="150"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="customerId"
                data-type="number"
                :width="100"
            />
            <DxColumn
                data-field="userId"
                data-type="number"
                :width="100"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="customerRegistration"
                data-type="date"
                :width="100"
                :allow-sorting="false"
            />
            <DxColumn
                data-field="discounts"
                data-type="string"
                :width="350"
            />
            <DxColumn
                :width="0"
            />

            <DxToolbar>
                <DxItem
                    location="before"
                    template="refreshTemplate"
                />
                <DxItem
                    location="before"
                    template="createdStartTemplate"
                />
                <DxItem
                    location="before"
                    template="createdEndTemplate"
                />
                <DxItem
                    location="before"
                    template="selectStatusesTemplate"
                />
                <DxItem
                    location="after"
                    template="exportTemplate"
                />
            </DxToolbar>

            <DxColumnChooser :enabled="true"/>
            <DxSelection mode="single"/>
            <DxExport
                :enabled="true"
                :allow-export-selected-data="true"
                :file-name="exportFileName"
            />
            <DxSearchPanel
                :visible="false"
                :highlight-case-sensitive="true"
            />
            <DxPager
                :allowed-page-sizes="pageSizes"
                :show-page-size-selector="true"
            />
            <DxPaging :page-size="50"/>

            <template #refreshTemplate>
                <DxButton
                    icon="refresh"
                    @click="reloadDataSource()"
                />
            </template>
            <template #createdStartTemplate>
                <DxDateBox
                    ref="createdStart"
                    v-model:value="createdStart"
                    @valueChanged="refreshDataSource()"
                    class="float-left mr-1"
                    style="max-width: 150px"
                    type="date"
                    display-format="shortDate"
                />
            </template>
            <template #createdEndTemplate>
                <DxDateBox
                    ref="createdEnd"
                    v-model:value="createdEnd"
                    @valueChanged="refreshDataSource()"
                    class="float-left mr-1"
                    style="max-width: 150px"
                    type="date"
                    display-format="shortDate"
                />
            </template>
            <template #selectStatusesTemplate>
                <DxDropDownBox
                    v-model:value="valuePaymentStatus"
                    :defer-rendering="false"
                    :show-clear-button="true"
                    :data-source="dataSourcePaymentStatuses"
                    :width="290"
                    display-expr="value"
                    value-expr="id"
                    placeholder="Статус оплаты..."
                >
                    <template #content="{ data }">
                        <DxDataGrid
                            :height="345"
                            :width="250"
                            :hover-state-enabled="true"
                            :column-auto-width="false"
                            key-expr="id"
                            :data-source="dataSourcePaymentStatuses"
                            :selected-row-keys="selectedRowKeysPaymentStatuses"
                            @selection-changed="onSelectionChangedPaymentStatuses"
                        >
                            <DxColumn
                                column="Код"
                                data-field="id"
                                data-type="number"
                                :width="50"
                            />
                            <DxColumn
                                column="Название"
                                data-field="value"
                                data-type="string"
                                :width="100"
                            />
                            <DxSelection mode="multiple"/>
                            <DxPaging
                                :enabled="true"
                                :page-size="100"
                            />
                        </DxDataGrid>
                    </template>
                </DxDropDownBox>
            </template>
            <template #exportTemplate>
                <DxButton
                    icon="xlsxfile"
                    @click="exportToExcel()"
                />
            </template>
        </DxDataGrid>
    </layout-main>
</template>
<script>
import moment from 'moment';

import {
    DxDataGrid,
    DxColumn,
    DxSelection,
    DxExport,
    DxPager,
    DxPaging,
    DxLoadPanel,
    DxToolbar,
    DxItem,
    DxColumnChooser,
    DxSearchPanel,
} from 'devextreme-vue/data-grid';

import DxDropDownBox from 'devextreme-vue/drop-down-box';
import DxDateBox from 'devextreme-vue/date-box';
import DxButton from 'devextreme-vue/button';

import DataSource from 'devextreme/data/data_source';
import 'devextreme/data/odata/store';
import ruMessages from "devextreme/localization/messages/ru.json";
import { locale, loadMessages } from "devextreme/localization";

export default {
    components: {
        DxDataGrid,
        DxColumn,
        DxSelection,
        DxExport,
        DxPager,
        DxPaging,
        DxLoadPanel,
        DxToolbar,
        DxItem,
        DxDropDownBox,
        DxButton,
        DxDateBox,
        DxColumnChooser,
        DxSearchPanel,
    },
    created() {
        loadMessages(ruMessages);
        locale('ru');
    },
    data() {
        const pathDataSource = '/api/analytics/dump-orders';
        const createdEnd = new Date();
        const createdStart = new Date();
        createdStart.setMonth(createdStart.getMonth() - 1);

        const dataSource = new DataSource({
            paginate: true,
            pageSize: 100,
            store: {
                url: pathDataSource,
                key: 'id',
                type: 'odata',
                version: 4,
                jsonp: true,
                beforeSend(request) {
                    request.timeout = 180000;
                    request.params.createdStart = moment(createdStart).format('YYYY-MM-DD');
                    request.params.createdEnd = moment(createdEnd).format('YYYY-MM-DD');
                },
            },
        });

        const dataSourcePaymentStatuses = [
            {id: 1, value: 'Не оплачено'},
            {id: 2, value: 'Оплачено'},
            {id: 3, value: 'Просрочено'},
            {id: 4, value: 'Средства захолдированы'},
            {id: 5, value: 'Ошибка проведения платежа'},
            {id: 6, value: 'Ожидает оплаты'},
        ];

        const valuePaymentStatus = [];

        return {
            createdStart,
            createdEnd,
            dataSource,
            pathDataSource,
            dataSourcePaymentStatuses,
            valuePaymentStatus,
            pageSizes: [50, 100, 500],
            gridHeight: "Calc(100Vh - 100px)",
            gridWidth: "100%",
            columnResizingMode: "widget",
            exportFileName: "dump-orders",
            exportFormats: ["xlsx"],
        };
    },
    computed: {
        selectedRowKeysPaymentStatuses() {
            return this.valuePaymentStatus;
        },
    },
    methods: {
        reloadDataSource() {
            this.dataSource.reload();
        },
        refreshDataSource() {
            let createdStart = moment(this.createdStart).format('YYYY-MM-DD');
            let createdEnd = moment(this.createdEnd).format('YYYY-MM-DD');
            let valuePaymentStatus = this.valuePaymentStatus;

            this.dataSource = new DataSource({
                paginate: true,
                pageSize: 100,
                store: {
                    url: this.pathDataSource,
                    key: 'id',
                    type: 'odata',
                    version: 4,
                    jsonp: true,
                    beforeSend(request) {
                        request.timeout = 180000;
                        request.params.createdStart = createdStart;
                        request.params.createdEnd = createdEnd;
                        if (valuePaymentStatus) {
                            request.params.paymentStatus = valuePaymentStatus;
                        }
                    },
                },
            });
        },
        onSelectionChangedPaymentStatuses({ selectedRowsData }) {
            this.valuePaymentStatus = selectedRowsData.map((item) => item.id);
            this.refreshDataSource();
        },
        exportToExcel() {
            const dataGrid = this.$refs.dataGrid.instance;
            dataGrid.exportToExcel(false);
        },
        onRowDblClick(e) {
            let data = e.data;
            if (data.orderId) {
                window.open('/orders/'+ data.orderId, '_blank');
            }
        },
    },
};
</script>
<style>
.dx-datagrid-content .dx-datagrid-table .dx-row .dx-command-select {
    width: 35px;
    min-width: 35px;
    max-width: 35px;
}
.dx-datagrid {
    color: rgb(0 0 0);
}
.dx-datagrid .dx-row > td {
    padding: 3px;
}
.dx-datagrid-headers {
    color: rgb(0 0 0);
    background: rgb(232 232 232);
}
</style>