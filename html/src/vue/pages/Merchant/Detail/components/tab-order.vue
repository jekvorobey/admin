<template>
    <div>
        {{ test }}
<!--        <div class="card">-->
<!--            <div class="card-header">-->
<!--                Фильтр-->
<!--                <button @click="toggleHiddenFilter" class="btn btn-sm btn-light float-right">-->
<!--                    {{ opened ? 'Меньше' : 'Больше' }} фильтров-->
<!--                    <fa-icon :icon="opened ? 'compress-arrows-alt' : 'expand-arrows-alt'"></fa-icon>-->
<!--                </button>-->
<!--            </div>-->
<!--            <div class="card-body">-->
<!--                Test-->
<!--            </div>-->
<!--        </div>-->

<!--        <table class="table table-condensed">-->
<!--            <thead>-->
<!--            <tr>-->
<!--                <th>-->
<!--                    <input type="checkbox"-->
<!--                           id="select-all-page-shipments"-->
<!--                           v-model="isSelectAllPageShipments"-->
<!--                           @click="selectAllPageShipments()"-->
<!--                    >-->
<!--                    <label for="select-all-page-shipments" class="mb-0">Все</label>-->
<!--                </th>-->
<!--                <th v-for="column in columns" v-if="column.isShown">{{column.name}}</th>-->
<!--                <th>-->
<!--                    <button class="btn btn-light float-right" @click="showChangeColumns">-->
<!--                        <fa-icon icon="cog"></fa-icon>-->
<!--                    </button>-->
<!--                    <modal-columns :i-columns="editedShowColumns"></modal-columns>-->
<!--                </th>-->
<!--            </tr>-->
<!--            </thead>-->
<!--            <tbody>-->
<!--            <tr v-for="order in orders">-->
<!--                <td><input type="checkbox" value="true" class="order-select" :value="order.id"></td>-->
<!--                <td v-for="column in columns" v-if="column.isShown">-->
<!--                    <template v-if="column.code === 'status'">-->
<!--                        <order-status :status='order.status'/>-->
<!--                        <template v-if="order.is_canceled">-->
<!--                            <br><span class="badge badge-danger">Отменен</span>-->
<!--                        </template>-->
<!--                    </template>-->
<!--                    <payment-status v-else-if="column.code === 'payment_status'" :status="order.payment_status"></payment-status>-->
<!--                    <div v-else v-html="column.value(order)"></div>-->
<!--                </td>-->
<!--                <td></td>-->
<!--            </tr>-->
<!--            <tr v-if="!orders.length">-->
<!--                <td :colspan="columns.length + 1">Заказов нет</td>-->
<!--            </tr>-->
<!--            </tbody>-->
<!--        </table>-->
    </div>
</template>

<script>
    import Services from "../../../../../scripts/services/services";

    // import modalMixin from '../../../../mixins/modal';
    // import ModalColumns from '../../../../components/modal-columns/modal-columns.vue';

    // const cleanHiddenFilter = {
    //     number: '',
    //     cost: [],
    //     offer_xml_id: '',
    //     product_vendor_code: '',
    //     brands: [],
    // };
    //
    // const cleanFilter = Object.assign({
    //     created_at: [],
    //     required_shipping_at: '',
    //     store_id: [],
    //     status: [],
    // }, cleanHiddenFilter);

    export default {
        name: 'tab-marketing',
        props: ['id'],
        components: {
            // ModalColumns
        },
        // mixins: [modalMixin],
        data() {
            // let filter = Object.assign({}, cleanFilter);
            return {
                // opened: false,
                // filter,
                // isSelectAllPageShipments: false,
                // columns: [
                //     {
                //         name: '№ заказа',
                //         code: 'number',
                //         value: function(order) {
                //             return '<a href="' + self.getRoute('orders.flowDetail', {id: order.id}) + '">' +
                //                 order.number + '</a>';
                //         },
                //         isShown: true,
                //         isAlwaysShown: true,
                //     },
                // ],
                // orders: [],
                test: null,
            }
        },
        created() {
            Services.showLoader();
            Promise.all([
                // Services.net().get(
                //     this.getRoute('merchant.detail.order.data', {id: this.id})
                // ),
                Services.net().get(
                    this.getRoute('merchant.detail.order.pagination', {id: this.id})
                ),
                // this.paginationPromise()
            ]).then(data => {
                // this.orders = data[0].orders;
                this.test = data[0].test;
            }).finally(() => {
                Services.hideLoader();
            });
        },
        methods: {
            // toggleHiddenFilter() {
            //     this.opened = !this.opened;
            //     if (this.opened === false) {
            //         for (let entry of Object.entries(cleanHiddenFilter)) {
            //             this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
            //         }
            //         this.applyFilter();
            //     }
            // },
            // selectAllPageShipments() {
            //     let checkboxes = document.getElementsByClassName('shipment-select');
            //     for (let i = 0; i < checkboxes.length; i++) {
            //         checkboxes[i].checked = this.isSelectAllPageShipments ? '' : 'checked';
            //     }
            // },
            // showChangeColumns() {
            //     this.openModal('list_columns');
            // },
        },
        computed: {
            // statusOptions() {
            //     return Object.values(this.shipmentStatuses).map(status => ({
            //         value: status.id,
            //         text: status.name
            //     }));
            // },
            // editedShowColumns() {
            //     return this.columns.filter(function(column) {
            //         return !column.isAlwaysShown;
            //     })
            // },
        },
    };
</script>