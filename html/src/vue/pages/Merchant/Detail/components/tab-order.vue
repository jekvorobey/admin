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
                    <f-input v-model="filter.order_number"  class="col-3">
                        № заказа
                    </f-input>
                    <f-input v-model="filter.number" class="col-3">
                        № отправления
                    </f-input>
                    <f-multi-select v-model="filter.status" :options="statusOptions" class="col-3">
                        Статус отправления текущий
                    </f-multi-select>
                    <f-select v-model="filter.is_problem" :options="problemOptions" class="col-3">
                        Проблемный
                    </f-select>
                </div>
                <transition name="slide">
                    <div v-if="opened" class="additional-filter pt-3 mt-3">
                        <div class="row">
                            <f-input v-model="filter.customer_id" type="number" class="col-3">
                                ID клиента
                            </f-input>
                            <f-input v-model="filter.customer_full_name" id="color" list="customersList" class="col-5">
                                ФИО клиента
                            </f-input>
                            <datalist id="customersList">
                                <option v-for="customerFullName in customerFullNames" :value="customerFullName"/>
                            </datalist>
                            <f-input v-model="filter.package_qty_from" type="number" class="col-2">
                                Кол-во коробок
                                <template #prepend><span class="input-group-text">от</span></template>
                                <template #append><span class="input-group-text">шт.</span></template>
                            </f-input>
                            <f-input v-model="filter.package_qty_to" type="number" class="col-2">
                                &nbsp;
                                <template #prepend><span class="input-group-text">до</span></template>
                                <template #append><span class="input-group-text">шт.</span></template>
                            </f-input>
                        </div>
                        <div class="row">
                            <f-input v-model="filter.weight_from" type="number" class="col-2">
                                Вес отправления
                                <template #prepend><span class="input-group-text">от</span></template>
                                <template #append><span class="input-group-text">кг</span></template>
                            </f-input>
                            <f-input v-model="filter.weight_to" type="number" class="col-2">
                                &nbsp;
                                <template #prepend><span class="input-group-text">до</span></template>
                                <template #append><span class="input-group-text">кг</span></template>
                            </f-input>
                            <f-input v-model="filter.cost_from" type="number" class="col-2">
                                Сумма товаров
                                <template #prepend><span class="input-group-text">от</span></template>
                                <template #append><span class="input-group-text">руб.</span></template>
                            </f-input>
                            <f-input v-model="filter.cost_to" type="number" class="col-2">
                                &nbsp;
                                <template #prepend><span class="input-group-text">до</span></template>
                                <template #append><span class="input-group-text">руб.</span></template>
                            </f-input>
                            <f-multi-select v-model="filter.delivery_type" :options="deliveryTypeOptions" class="col-4">
                                Тип доставки
                            </f-multi-select>
                        </div>
                        <div class="row">
                            <f-multi-select v-model="filter.delivery_method" :options="deliveryMethodOptions" class="col-3">
                                Способ доставки
                            </f-multi-select>
                            <f-multi-select v-model="filter.delivery_service" :options="deliveryServiceOptions" class="col-3">
                                ЛО - последняя миля
                            </f-multi-select>
                            <f-multi-select v-model="filter.delivery_service_zero_mile" :options="deliveryServiceOptions" class="col-3">
                                ЛО - нулевая миля
                            </f-multi-select>
                            <f-multi-select v-model="filter.store_id" :options="storeOptions" class="col-3">
                                Склад отгрузки
                            </f-multi-select>
                        </div>
                        <div class="row">
                            <div class="col">Адрес доставки:</div>
                        </div>
                        <div class="row">
                            <f-input v-model="filter.delivery_address_post_index" class="col-3">
                                Индекс
                            </f-input>
                            <f-input v-model="filter.delivery_address_region" class="col-5">
                                Регион
                            </f-input>
                            <f-input v-model="filter.delivery_address_city" class="col-4">
                                Город
                            </f-input>
                        </div>
                        <div class="row">
                            <f-input v-model="filter.delivery_address_street" class="col-8">
                                Улица
                            </f-input>
                            <f-input v-model="filter.delivery_address_house" class="col-1">
                                Дом
                            </f-input>
                            <f-input v-model="filter.delivery_address_porch" class="col-1">
                                Под.
                            </f-input>
                            <f-input v-model="filter.delivery_address_floor" class="col-1">
                                Этаж
                            </f-input>
                            <f-input v-model="filter.delivery_address_flat" class="col-1">
                                Кв.
                            </f-input>
                        </div>
                        <div class="row">
                            <f-date v-model="filter.psd" class="col-6" range confirm>
                                PSD - Дата отгрузки отправления
                            </f-date>
                            <f-date v-model="filter.delivery_at" class="col-6" range confirm>
                                PDD - Дата доставки отправления плановая
                            </f-date>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>

        <table class="table table-condensed table-responsive">
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
    import FSelect from '../../../../components/filter/f-select.vue';
    import FDate from '../../../../components/filter/f-date.vue';

    import Services from "../../../../../scripts/services/services";

    import modalMixin from '../../../../mixins/modal';
    import ModalColumns from '../../../../components/modal-columns/modal-columns.vue';
    import {integer} from "../../../../../scripts/filters";

    const cleanHiddenFilter = {
        customer_id: null,
        customer_full_name: '',
        package_qty_from: null,
        package_qty_to: null,
        weight_from: null,
        weight_to: null,
        cost_from: null,
        cost_to: null,
        delivery_type: [],
        delivery_method: [],
        delivery_service: [],
        delivery_service_zero_mile: [],
        store_id: [],
        delivery_address_post_index: '',
        delivery_address_region: '',
        delivery_address_city: '',
        delivery_address_street: '',
        delivery_address_porch: '',
        delivery_address_house: '',
        delivery_address_floor: '',
        delivery_address_flat: '',
        psd: [],
        delivery_at: [],
    };

    const cleanFilter = Object.assign({
        order_number: '',
        number: '',
        status: [],
        is_problem: null,
    }, cleanHiddenFilter);

    const serverKeys = [
        'order_number',
        'number',
        'status',
        'is_problem',
        'customer_id',
        'customer_full_name',
        'package_qty_from',
        'package_qty_to',
        'weight_from',
        'weight_to',
        'cost_from',
        'cost_to',
        'delivery_type',
        'delivery_method',
        'delivery_service',
        'delivery_service_zero_mile',
        'store_id',
        'delivery_address_post_index',
        'delivery_address_region',
        'delivery_address_city',
        'delivery_address_street',
        'delivery_address_porch',
        'delivery_address_house',
        'delivery_address_floor',
        'delivery_address_flat',
        'psd',
        'delivery_at',
    ];

    export default {
        name: 'tab-order',
        props: ['id'],
        components: {
            FInput,
            FMultiSelect,
            FSelect,
            FDate,
            ModalColumns
        },
        mixins: [modalMixin],
        data() {
            let self = this;
            let filter = Object.assign({}, cleanFilter);
            filter.status = filter.status.map(value => parseInt(value));

            return {
                opened: false,
                filter,
                appliedFilter: {},
                shipmentStatuses: {},
                customerFullNames: [],
                deliveryTypes: [],
                deliveryMethods: [],
                deliveryServices: [],
                stores: [],
                isSelectAllPageShipments: false,
                currentPage: 1,
                pager: {},
                columns: [
                    {
                        name: '№ заказа',
                        code: 'order',
                        value: function(shipment) {
                            return '<a href="' + self.getRoute('orders.detail', {id: shipment.order.id}) + '">' +
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
                        code: 'customer',
                        value: function(shipment) {
                            return shipment.customer.id !== 'N/A' ?
                                '<a href="' + self.getRoute('customers.detail', {id: shipment.customer.id}) +
                                '">' + shipment.customer.id + ': ' + shipment.customer.full_name + '</a>' :
                                shipment.customer.id + ': ' + shipment.customer.full_name;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Статус отправления текущий',
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
                            return integer(shipment.weight);
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
                            return shipment.psd;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'PDD - Дата доставки отправления плановая',
                        code: 'psd',
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
                Services.net().get(
                    this.getRoute('merchant.detail.order.data', {id: this.id})
                ),
                this.paginationPromise(),
            ]).then(data => {
                this.shipmentStatuses = data[0].shipmentStatuses;
                this.customerFullNames = data[0].customerFullNames;
                this.deliveryTypes = data[0].deliveryTypes;
                this.deliveryMethods = data[0].deliveryMethods;
                this.deliveryServices = data[0].deliveryServices;
                this.stores = data[0].stores;
                this.shipments = data[1].shipments;
                this.pager = data[1].pager;
            }).finally(() => {
                Services.hideLoader();
            });
        },
        methods: {
            toggleHiddenFilter() {
                this.opened = !this.opened;
                if (this.opened === false) {
                    for (let entry of Object.entries(cleanHiddenFilter)) {
                        this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                    }
                    this.applyFilter();
                }
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
                    if (data.pager) {
                        this.pager = data.pager
                    }
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
                return Object.values(this.shipmentStatuses).map(status => ({
                    value: status.id,
                    text: status.name
                }));
            },
            problemOptions() {
                return [
                    {
                        value: false,
                        text: 'Нет проблем'
                    },
                    {
                        value: true,
                        text: 'Проблемный'
                    }
                ];
            },
            deliveryTypeOptions() {
                return Object.values(this.deliveryTypes).map(type => ({
                    value: type.id,
                    text: type.name
                }));
            },
            deliveryMethodOptions() {
                return Object.values(this.deliveryMethods).map(method => ({
                    value: method.id,
                    text: method.name
                }));
            },
            deliveryServiceOptions() {
                return Object.values(this.deliveryServices).map(service => ({
                    value: service.id,
                    text: service.name
                }));
            },
            storeOptions() {
                return Object.values(this.stores).map(store => ({
                    value: store.id,
                    text: store.name
                }));
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
<style scoped>
    .additional-filter {
        border-top: 1px solid #DFDFDF;
    }
</style>