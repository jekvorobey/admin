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

            @toolbar-preparing="onToolbarPreparing"
            @row-dbl-click="onRowDblClick"
        >
            <DxColumn
                data-field="brandName"
                data-type="string"
                :width="150"
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
            />
            <DxColumn
                data-field="merchantName"
                data-type="string"
                :width="150"
            />
            <DxColumn
                data-field="offerId"
                data-type="number"
                :width="80"
            />
            <DxColumn
                data-field="productId"
                data-type="number"
                :width="80"
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
                :width="80"
                :allow-sorting="false"
            >
                <DxFormat
                    type="fixedPoint"
                    :precision="0"
                />
            </DxColumn>
            <DxColumn
                data-field="price"
                data-type="number"
                :width="80"
                :allow-sorting="false"
            >
                <DxFormat
                    type="fixedPoint"
                    :precision="0"
                />
            </DxColumn>
            <DxColumn
                data-field="priceWithDiscount"
                data-type="number"
                :width="80"
                :allow-sorting="false"
            >
                <DxFormat
                    type="fixedPoint"
                    :precision="0"
                />
            </DxColumn>
            <DxColumn
                data-field="percentDiscount"
                data-type="number"
                :width="80"
                :allow-sorting="false"
            >
                <DxFormat
                    type="percent"
                    :precision="2"
                />
            </DxColumn>
            <DxColumn
                :width="0"
            />

            <DxColumnChooser :enabled="true"/>
            <DxSelection mode="single"/>
            <DxExport
                :enabled="true"
                :allow-export-selected-data="false"
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
    DxFormat,
} from 'devextreme-vue/data-grid';

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
        DxColumnChooser,
        DxSearchPanel,
        DxFormat,
    },
    created() {
        loadMessages(ruMessages);
        locale('ru');
    },
    data() {
        const pathDataSource = '/api/analytics/competition';
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
                    request.params.createdStart = moment(createdStart).format('YYYY-MM-DD');
                    request.params.createdEnd = moment(createdEnd).format('YYYY-MM-DD');
                },
            },
        });

        const valuePaymentStatus = [];

        return {
            createdStart,
            createdEnd,
            dataSource,
            pathDataSource,
            valuePaymentStatus,
            pageSizes: [50, 100, 500],
            gridHeight: "Calc(100Vh - 100px)",
            gridWidth: "100%",
            columnResizingMode: "widget",
            exportFileName: "competition",
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
                        request.params.createdStart = createdStart;
                        request.params.createdEnd = createdEnd;
                    },
                },
            });
        },
        exportToExcel() {
            const dataGrid = this.$refs.dataGrid.instance;
            dataGrid.exportToExcel();
        },
        onRowDblClick(e) {
            let data = e.data;
            if (data.offerId) {
                window.open('/offers/'+ data.offerId, '_blank');
            }
        },
        onToolbarPreparing(e) {
            let dataGrid = e.component;
            e.toolbarOptions.items.unshift({
                location: "before",
                widget: "dxButton",
                options: {
                    icon: "refresh",
                    onClick: function() {
                        dataGrid.refresh();
                    }
                }
            });
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