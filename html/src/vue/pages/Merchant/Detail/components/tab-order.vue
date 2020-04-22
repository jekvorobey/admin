<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
                <button @click="toggleHiddenFilter" class="btn btn-sm btn-light float-right">
                    {{ opened ? 'Меньше' : 'Больше' }} фильтров
                    <fa-icon :icon="opened ? 'compress-arrows-alt' : 'expand-arrows-alt'"></fa-icon>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.order_number" class="col-2">
                        № заказа
                    </f-input>
                    <f-input v-model="filter.number" class="col-2">
                        № отправления
                    </f-input>
                    status
<!--                    <f-multi-select v-model="filter.status" :options="statusOptions" class="col">-->
<!--                        Статус заказа-->
<!--                    </f-multi-select>-->
                </div>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           id="select-all-page-shipments"
                           v-model="isSelectAllPageShipments"
                           @click="selectAllPageShipments()"
                    >
                    <label for="select-all-page-shipments" class="mb-0">Все</label>
                </th>
                <th v-for="column in columns" v-if="column.isShown">{{column.name}}</th>
                <th>
                    <button class="btn btn-light float-right" @click="showChangeColumns">
                        <fa-icon icon="cog"></fa-icon>
                    </button>
                    <modal-columns :i-columns="editedShowColumns"></modal-columns>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="shipment in shipments">
                <td><input type="checkbox" value="true" class="shipment-select" :value="shipment.id"></td>
                <td v-for="column in columns" v-if="column.isShown" v-html="column.value(shipment)"></td>
            </tr>
            <tr v-if="!shipments.length">
                <td :colspan="columns.length + 1">Заказов нет</td>
            </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="pager.pages > 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                :hide-goto-end-buttons="pager.pages < 10"
                class="float-right"
        ></b-pagination>
    </div>
</template>

<script>
    import FInput from '../../../../components/filter/f-input.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';

    import Services from "../../../../../scripts/services/services";

    import modalMixin from '../../../../mixins/modal';
    import ModalColumns from '../../../../components/modal-columns/modal-columns.vue';

    const cleanHiddenFilter = {
        client: null,
        package_qty: null,
        weight: null,
        cost: null,
        delivery_type: null,
        delivery_method: null,
        delivery_service_last_mile: null,
        delivery_service_zero_mile: null,
        delivery_address: '',
        required_shipping_at: '',
        delivery_at: '',
    };

    const cleanFilter = Object.assign({
        order_number: '',
        number: '',
        status: null,
        is_problem: null,
    }, cleanHiddenFilter);

    const serverKeys = [
        'order_number',
        'number',
        'status',
        'is_problem',
        'client',
        'package_qty',
        'weight',
        'cost',
        'delivery_type',
        'delivery_method',
        'delivery_service_last_mile',
        'delivery_service_zero_mile',
        'delivery_address',
        'required_shipping_at',
        'delivery_at',
    ];

    export default {
        name: 'tab-order',
        props: ['id'],
        components: {
            FInput,
            FMultiSelect,
            ModalColumns
        },
        mixins: [modalMixin],
        data() {
            let self = this;
            let filter = Object.assign({}, cleanFilter);

            return {
                opened: false,
                filter,
                shipmentStatuses: {},
                isSelectAllPageShipments: false,
                currentPage: 1,
                pager: {},
                columns: [
                    {
                        name: '№ заказа',
                        code: 'order',
                        value: function(shipment) {
                            return '<a href="' + self.getRoute('orders.flowDetail', {id: shipment.order.id}) + '">' +
                                shipment.order.number + '</a>';
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: '№ отправления',
                        code: 'shipment',
                        value: function(shipment) {
                            return shipment.shipment.number;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'ФИО + ID клиента',
                        code: 'client',
                        value: function(shipment) {
                            return '<a href="' + self.getRoute('customers.detail', {id: shipment.client.id}) + '">' +
                                shipment.client.id + ': ' + shipment.client.full_name + '</a>';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Статус',
                        code: 'status',
                        value: function(shipment) {
                            let status = '<span class="badge ' + self.statusClass(shipment.status.id) + '">' + shipment.status.name + '</span>';
                            if (shipment.is_problem) {
                                status += '<br><span class="badge badge-warning">Проблемный</span>';
                            }

                            return status;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Кол-во коробок',
                        code: 'package_qty',
                        value: function(shipment) {
                            return shipment.package_qty;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Вес отправления',
                        code: 'weight',
                        value: function(shipment) {
                            return shipment.weight;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Сумма',
                        code: 'cost',
                        value: function(shipment) {
                            return self.preparePrice(shipment.cost);
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Тип доставки',
                        code: 'delivery_type',
                        value: function(shipment) {
                            return shipment.delivery_type.name;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Способ доставки',
                        code: 'delivery_method',
                        value: function(shipment) {
                            return shipment.delivery_method.name;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'ЛО - последняя миля',
                        code: 'delivery_service_last_mile',
                        value: function(shipment) {
                            return shipment.delivery_service_last_mile.name;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'ЛО - нулевая миля',
                        code: 'delivery_service_zero_mile',
                        value: function(shipment) {
                            return shipment.delivery_service_zero_mile.name;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Склад отгрузки',
                        code: 'store',
                        value: function(shipment) {
                            return shipment.store.name;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Адрес прибытия',
                        code: 'delivery_address',
                        value: function(shipment) {
                            return shipment.delivery_address;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'PSD - Дата отгрузки отправления',
                        code: 'delivery_address',
                        value: function(shipment) {
                            return shipment.required_shipping_at;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'PDD - Дата доставки отправления плановая',
                        code: 'required_shipping_at',
                        value: function(shipment) {
                            return shipment.delivery_at;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                ],
                shipments: [],
            }
        },
        created() {
            Services.showLoader();
            Promise.all([
                // Services.net().get(
                //     this.getRoute('merchant.detail.order.data', {id: this.id})
                // ),
                this.paginationPromise(),
                // this.paginationPromise()
            ]).then(data => {
                // this.orders = data[0].orders;
                this.shipments = data[0].shipments;
                this.pager = data[0].pager;
            }).finally(() => {
                Services.hideLoader();
            });
        },
        methods: {
            toggleHiddenFilter() {
                this.opened = !this.opened;
                // if (this.opened === false) {
                //     for (let entry of Object.entries(cleanHiddenFilter)) {
                //         this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                //     }
                //     this.applyFilter();
                // }
            },
            selectAllPageShipments() {
                let checkboxes = document.getElementsByClassName('shipment-select');
                for (let i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = this.isSelectAllPageShipments ? '' : 'checked';
                }
            },
            showChangeColumns() {
                this.openModal('list_columns');
            },
            statusClass(statusId) {
                switch (statusId) {
                    case 4: return 'badge-info';
                    case 5: return 'badge-primary';
                    case 6: return 'badge-success';
                    default: return 'badge-light';
                }
            },
            paginationPromise() {
                return Services.net().get(
                    this.getRoute('merchant.detail.order.pagination', {id: this.id}),
                    {
                        page: this.currentPage,
                        filter: this.appliedFilter,
                    }
                );
            },
            loadPage() {
                Services.showLoader();
                this.paginationPromise().then(data => {
                    this.shipments = data.shipments;
                    this.pager = data.pager;
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            applyFilter() {
                let tmpFilter = {};
                for (let [key, value] of Object.entries(this.filter)) {
                    if (value && serverKeys.indexOf(key) !== -1) {
                        tmpFilter[key] = value;
                    }
                }
                this.appliedFilter = tmpFilter;
                this.currentPage = 1;
                this.loadPage();
            },
            clearFilter() {
                for (let entry of Object.entries(cleanFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.applyFilter();
            },
        },
        computed: {
            statusOptions() {
                // return Object.values(this.shipmentStatuses).map(status => ({
                //     value: status.id,
                //     text: status.name
                // }));
            },
            editedShowColumns() {
                return this.columns.filter(function(column) {
                    return !column.isAlwaysShown;
                })
            },
        },
        watch: {
            currentPage() {
                this.loadPage();
            }
        }
    };
</script>