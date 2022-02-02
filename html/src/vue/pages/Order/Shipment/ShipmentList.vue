<template>
    <layout-main>
        <b-card>
            <template v-slot:header>
                Фильтр
                <button @click="toggleHiddenFilter" class="btn btn-sm btn-light float-right">
                    {{ opened ? 'Меньше' : 'Больше' }} фильтров
                    <fa-icon :icon="opened ? 'compress-arrows-alt' : 'expand-arrows-alt'"></fa-icon>
                </button>
            </template>
            <div class="row">
                <f-input v-model="filter.number" class="col-sm-12 col-md-4 col-xl-3">
                    № Отправления
                </f-input>
                <f-input v-model="filter.customer" class="col-sm-12 col-md-4 col-xl-5">
                    ФИО, e-mail или телефон покупателя
                </f-input>
                <f-multi-select v-model="filter.status" :options="statusOptions" class="col-sm-12 col-md-4 col-xl-3">
                    Статус Отправления
                </f-multi-select>
            </div>
            <transition name="slide">
                <div v-if="opened">
                    <div class="additional-filter pt-3 mt-3">
                        <div class="row">
                            <f-input v-model="filter.price_from" type="number" class="col-sm-12 col-md-3">
                                Сумма Отправления
                                <template #prepend><span class="input-group-text">от</span></template>
                                <template #append><span class="input-group-text">руб.</span></template>
                            </f-input>
                            <f-input v-model="filter.price_to" type="number" class="col-sm-12 col-md-3">
                                &nbsp;
                                <template #prepend><span class="input-group-text">до</span></template>
                                <template #append><span class="input-group-text">руб.</span></template>
                            </f-input>
                            <f-input v-model="filter.product_vendor_code" type="text" class="col-sm-12 col-md-3">
                                Артикул
                                <template #help>Артикул товара в системе iBT</template>
                            </f-input>
                            <f-multi-select v-model="filter.brands" :options="brandOptions" class="col-sm-12 col-md-3">
                              Бренд
                              <template #help>Будут показаны заказы в которых есть товары указанного бренда</template>
                            </f-multi-select>
                        </div>
                        <div class="row">
                            <f-multi-select v-model="filter.merchants" :options="merchantOptions" class="col-sm-12 col-md-3">
                                Мерчант
                                <template #help>Будут показаны заказы в которых есть товары указанного мерчанта</template>
                            </f-multi-select>
                            <f-multi-select v-model="filter.stores" :options="storeOptions" class="col-sm-12 col-md-4">
                                Склад отгрузки
                            </f-multi-select>
                            <f-multi-select v-model="filter.delivery_type" :options="deliveryTypeOptions" class="col-sm-12 col-md-2">
                              Тип доставки
                            </f-multi-select>
                            <f-multi-select v-model="filter.delivery_service" :options="deliveryServiceOptions" class="col-sm-12 col-md-3">
                              Логистический оператор
                            </f-multi-select>
                        </div>
                        <div class="row">
                            <v-dadata
                                :value.sync="filter.delivery_address_city"
                                bounds="city-settlement"
                                @onSelect="onDeliveryCitySelect"
                                class="col-sm-12 col-md-4"
                            >Город доставки</v-dadata>
                            <f-date v-model="filter.psd" class="col-sm-12 col-md-4" range confirm>
                                PSD
                            </f-date>
                            <f-date v-model="filter.pdd" class="col-sm-12 col-md-4" range confirm>
                                PDD
                            </f-date>
                        </div>
                        <div class="row">
                            <f-input v-model="filter.cargo_id" type="text" class="col-sm-12 col-md-6">
                              Номер Груза
                            </f-input>
                            <f-input v-model="filter.cargo_xml_id" type="text" class="col-sm-12 col-md-6">
                              Номер задания на забор груза
                            </f-input>
                        </div>
                    </div>
                </div>
            </transition>

            <template v-slot:footer>
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего отправлений: {{ pager.total }}.</span>
            </template>
        </b-card>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <div>
                <a :href="getRoute('orders.create')" class="btn btn-success mt-3">Создать заказ</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-condensed">
                <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all-page-shipments" v-model="isSelectAllPageShipments" @click="selectAllPageShipments()">
                        <label for="select-all-page-shipments" class="mb-0">Все</label>
                    </th>
                    <th v-for="column in columns" v-if="column.isShown">
                        <span v-html="column.name"></span>
                        <fa-icon v-if="column.description" icon="question-circle"
                                 v-b-popover.hover="column.description"></fa-icon>
                    </th>
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
                    <td v-for="column in columns" v-if="column.isShown">
                        <div v-html="column.value(shipment)"></div>
                    </td>
                    <td></td>
                </tr>
                <tr v-if="!shipments.length">
                    <td :colspan="columns.length + 1">Отправлений нет</td>
                </tr>
                </tbody>
            </table>
        </div>
        <b-pagination
            v-if="pager.pages > 1"
            v-model="currentPage"
            :total-rows="pager.total"
            :per-page="pager.pageSize"
            @change="changePage"
            :hide-goto-end-buttons="pager.pages < 10"
            class="float-right"
        ></b-pagination>
    </layout-main>
</template>

<script>
import Services from '../../../../scripts/services/services';
import withQuery from 'with-query';
import qs from 'qs';
import {mapGetters} from 'vuex';

import FInput from '../../../components/filter/f-input.vue';
import FDate from '../../../components/filter/f-date.vue';
import FMultiSelect from '../../../components/filter/f-multi-select.vue';
import FSelect from '../../../components/filter/f-select.vue';
import VDadata from '../../../components/controls/VDaData/VDaData.vue';
import Dropdown from '../../../components/dropdown/dropdown.vue';
import Helpers from '../../../../scripts/helpers.js';
import ModalColumns from '../../../components/modal-columns/modal-columns.vue';

import modalMixin from '../../../mixins/modal.js';

const cleanHiddenFilter = {
    price_from: '',
    price_to: '',
    product_vendor_code: '',
    brands: [],
    merchants: [],
    stores: [],
    delivery_type: [],
    delivery_service: [],
    delivery_address_city: '',
    cargo_id: '',
    cargo_xml_id: '',
    psd: '',
    pdd: '',
    customer_id: [],
};

const cleanFilter = Object.assign({
    number: '',
    customer: '',
    status: [],
}, cleanHiddenFilter);

const serverKeys = [
    'number',
    'customer',
    'status',

    'price_from',
    'price_to',
    'product_vendor_code',
    'brands',
    'merchants',
    'stores',
    'delivery_type',
    'delivery_service',
    'delivery_address_city',
    'cargo_id',
    'cargo_xml_id',
    'psd',
    'pdd',
    'customer_id'
];

export default {
    mixins: [modalMixin],
    props: [
        'iShipments',
        'iOrders',
        'iCurrentPage',
        'iPager',
        'iMerchants',
        'iCustomers',
        'iUsers',
        'iBrands',
        'confirmationTypes',
        'iFilter',
        'iSort',
        'iStores',
    ],
    components: {
        FInput,
        FDate,
        FMultiSelect,
        FSelect,
        VDadata,
        Dropdown,
        ModalColumns,
    },
    data() {
        let self = this;
        let filter = Object.assign({}, cleanFilter, this.iFilter);
        filter.status = filter.status.map(value => parseInt(value));
        filter.delivery_type = filter.delivery_type.map(value => parseInt(value));
        filter.delivery_service = filter.delivery_service.map(value => parseInt(value));
        filter.brands = filter.brands.map(value => parseInt(value));
        filter.merchants = filter.merchants.map(value => parseInt(value));
        filter.stores = filter.stores.map(value => parseInt(value));
        return {
            opened: false,
            currentPage: this.iCurrentPage,
            shipments: this.iShipments,
            merchants: this.iMerchants,
            customers: this.iCustomers,
            users: this.iUsers,
            stores: this.iStores,
            orders: this.iOrders,
            brands: this.iBrands,
            filter,
            sort: this.iSort,
            appliedFilter: {},
            pager: this.iPager,
            isSelectAllPageShipments: false,
            invalidData: '<b class="text-warning">N/A</b>',
            columns: [
                {
                    name: '№ Отправления',
                    code: 'number',
                    value: (shipment) => {
                        return shipment.number;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Дата отправления',
                    code: 'shipment_created_at',
                    value: (shipment) => {
                        return this.datetimePrint(shipment.created_at);
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Статус Отправления',
                    code: 'status',
                    value: (shipment) => {
                        return Object.values(this.shipmentStatuses).find(item =>
                            item.id === shipment.status
                        ).name;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Сумма Отправления',
                    code: 'cost',
                    value: (shipment) => {
                        return Helpers.roundValue(shipment.delivery.cost) + ' руб.';
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'ЛО',
                    code: 'delivery_service',
                    value: (shipment) => {
                        return Object.values(this.deliveryServices).find(item =>
                            item.id === shipment.delivery.delivery_service
                        ).name;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Склад отгрузки',
                    code: 'store_id',
                    value: (shipment) => {
                        return this.getStore(this.stores[shipment.store_id]);
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Тип доставки',
                    code: 'delivery_type',
                    value: (shipment) => {
                        return Object.values(this.deliveryTypes).find(item =>
                            item.id === this.orders[shipment.delivery.order_id].delivery_type
                        ).name;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Адрес прибытия',
                    code: 'delivery_address',
                    value: (shipment) => {
                        return shipment.delivery.delivery_address.length !== 0 ?
                            '<small>' + shipment.delivery.delivery_address.address_string + '</small>'
                            : this.invalidData
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Клиент',
                    code: 'customer',
                    value: (shipment) => {
                        return this.showCustomer(shipment)
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Статус Оплаты',
                    code: 'payment_status',
                    value: (shipment) => {
                        return Object.values(this.shipmentStatuses).find(item =>
                            item.id === shipment.payment_status
                        ).name;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Вес',
                    code: 'weight',
                    value: (shipment) => {
                        return shipment.delivery.weight + 'г';
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: '№ заказа',
                    code: 'order_id',
                    value: (shipment) => {
                        return this.getOrder(shipment.delivery);
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },

                {
                    name: 'PSD',
                    description: 'Дата отгрузки',
                    code: 'psd',
                    value: (shipment) => {
                        return shipment.psd ? this.datetimePrint(shipment.psd) : this.invalidData
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'PDD',
                    description: 'Дата доставки плановая',
                    code: 'pdd',
                    value: (shipment) => {
                        return shipment.delivery.pdd ? this.datetimePrint(shipment.delivery.pdd) : this.invalidData
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Номер груза',
                    code: 'cargo_id',
                    value: (shipment) => {
                        return this.showCargo(shipment.cargo_id);
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Статус груза',
                    code: 'cargo_status',
                    value: (shipment) => {
                        if(shipment.cargo) {
                            return Object.values(this.cargoStatuses).find(item =>
                                item.id === shipment.cargo.status
                            ).name;
                        }

                        return this.invalidData;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Номер задания на забор груза',
                    code: 'cargo_xml_id',
                    value: (shipment) => {
                        return shipment.cargo ? this.cargoTrackNumber(shipment.cargo) : this.invalidData;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Фактическая дата доставки',
                    description: 'Дата доставки фактическая',
                    code: 'delivery_time_end',
                    value: (shipment) => {
                        return shipment.delivery_time_end ? this.datetimePrint(shipment.delivery_time_end) : this.invalidData
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
            ],
        };
    },
    methods: {
        changePage(newPage) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: newPage,
                filter: this.appliedFilter,
                sort: this.sort
            }));
        },
        loadPage() {
            Services.showLoader();
            Services.net().get(this.route('shipment.pagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
                sort: this.sort,
            }).then(data => {
                this.shipments = data.iShipments;
                this.merchants = data.iMerchants;
                this.customers = data.iCustomers;
                this.users = data.iUsers;
                this.stores = data.iStores;
                this.orders = data.iOrders;
                if (data.iPager) {
                    this.pager = data.iPager
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
            this.changePage(1);
            this.loadPage();
        },
        clearFilter() {
            for (let entry of Object.entries(cleanFilter)) {
                this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
            }
            this.applyFilter();
        },
        toggleHiddenFilter() {
            this.opened = !this.opened;
            if (this.opened === false) {
                for (let entry of Object.entries(cleanHiddenFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.applyFilter();
            }
        },
        isHiddenFilterDefaultOpen() {
            for (let entry of Object.entries(cleanHiddenFilter)) {
                if (!Helpers.isEqual(entry[1], this.filter[entry[0]]) && entry[1] !== this.filter[entry[0]]) {
                    return true;
                }
            }
            return false;
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
        onDeliveryCitySelect(suggestion) {
            let address = suggestion.data;

            this.filter.delivery_address_city = address.settlement_fias_id ? address.settlement_fias_id :
                address.city_fias_id;
        },
        /**
         * Получить пользователя, которому принадлежит посылка
         * @param shipment
         * @return string|html
         */
        showCustomer(shipment) {
            // ФИО клиента для отображения
            let user = this.users[
                this.customers[this.orders[shipment.delivery.order_id].customer_id].user_id
                ];

            if (user) {
              // Ссылка на страницу клиента в системе iBT
                let link = this.getRoute(
                    'customers.detail',
                    {id: this.orders[shipment.delivery.order_id].customer_id});

                return `<a href="${link}">${user.full_name}</a>`
            }
            return this.invalidData;
        },
        showCargo(cargoId) {
            if (cargoId) {
                let route = this.getRoute('cargo.detail', {id: cargoId});

                return `<a href="${route}">${cargoId}<a/>`
            }
            return '';
        },
        getOrder(delivery) {
            if (delivery) {
                let route = this.getRoute('orders.detail', {id: delivery.order_id});

                return `<a href="${route}">${delivery.order_id}<a/>`
            }

            return this.invalidData;
        },
        getStore(store) {
            if (store) {
                let route = this.getRoute('merchantStore.edit', {id: store.id});

                return `<a href="${route}">${store.name}<a/>`
            }

            return this.invalidData;
        },
        cargoTrackNumber(cargo) {
            if (cargo.cdek_intake_number) {
                return cargo.cdek_intake_number;
            }
            return cargo.xml_id;
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        statusOptions() {
            return Object.values(this.shipmentStatuses).map(status => ({value: status.id, text: status.name}));
        },
        deliveryTypeOptions() {
            return Object.values(this.deliveryTypes).map(type => ({value: type.id, text: type.name}));
        },
        brandOptions() {
            return Object.values(this.brands).map(brand => ({value: brand.id, text: brand.name}));
        },
        deliveryServiceOptions() {
            return Object.values(this.deliveryServices).map(service => ({value: service.id, text: service.name}));
        },
        merchantOptions() {
            return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.legal_name}));
        },
        storeOptions() {
            return Object.values(this.stores).map(store => ({value: store.id, text: store.address.address_string}));
        },
        booleanOptions() {
            return [{value: 0, text: 'Нет'}, {value: 1, text: 'Да'}];
        },
        editedShowColumns() {
            return this.columns.filter(function(column) {
                return !column.isAlwaysShown;
            })
        },
    },
    created() {
        window.onpopstate = () => {
            let query = qs.parse(document.location.search.substr(1));
            if (query.page) {
                this.currentPage = query.page;
            }
        };
        this.opened = this.isHiddenFilterDefaultOpen();
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