<template>
    <layout-main>
        <div class="card">
            <div class="card-header">
                Фильтр
                <button @click="toggleHiddenFilter" class="btn btn-sm btn-light float-right">
                    {{ opened ? 'Меньше' : 'Больше' }} фильтров!
                    <fa-icon :icon="opened ? 'compress-arrows-alt' : 'expand-arrows-alt'"></fa-icon>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.number" class="col-2">
                        № заказа
                    </f-input>
                    <f-input v-model="filter.customer" class="col">
                        ФИО, E-mail или телефон покупателя
                    </f-input>
                    <f-date v-model="filter.created" class="col" range confirm>
                        Дата заказа
                    </f-date>
                    <f-date v-model="filter.deliveryTime" class="col" range confirm>
                        Дата доставки
                    </f-date>
                    <f-select v-model="filter.orderStatus" :options="statusOptions" class="col">
                        Статус
                    </f-select>
                </div>

                <div>

                </div>
                <transition name="slide">
                    <div v-if="opened">
                        <div class="additional-filter pt-3 mt-3">
                            <div class="row">
                                <f-input type="number" class="col-2">
                                    Сумма заказа
                                    <template #prepend><span class="input-group-text">от</span></template>
                                    <template #append><span class="input-group-text">руб.</span></template>
                                </f-input>
                                <f-input type="number" class="col-2">
                                    &nbsp;
                                    <template #prepend><span class="input-group-text">до</span></template>
                                    <template #append><span class="input-group-text">руб.</span></template>
                                </f-input>
                                <f-input type="text" class="col-2">
                                    Код товара из ERP
                                    <template #help>Код из внешней системы, по которому импортируется товар</template>
                                </f-input>
                                <f-input type="text" class="col-2">
                                    Артикул
                                    <template #help>Артикул товара в системе iBT</template>
                                </f-input>
                            </div>
                            <div class="row">
                                <f-multi-select v-model="filter.brands" :options="brandOptions" class="col-4">
                                    Бренд
                                    <template #help>Будут показаны заказы в которых есть товары указанного бренда</template>
                                </f-multi-select>
                                <f-multi-select v-model="filter.payType" :options="paymentMethodOptions" class="col-3">
                                    Способ оплаты
                                </f-multi-select>
                                <f-multi-select v-model="filter.deliveryCity" :options="deliveryCityOptions" class="col-3">
                                    Город доставки
                                </f-multi-select>
                                <f-multi-select v-model="filter.deliveryMethod" :options="deliveryMethodOptions" class="col">
                                    Способ доставки
                                </f-multi-select>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <div class="action-bar d-flex justify-content-start">
                <dropdown :items="dropdownItems" @select="downloadDoc" class="mr-4 order-btn">
                    <fa-icon icon="file-download"></fa-icon>
                    Скачать документы
                </dropdown>
                <dropdown :items="dropdownItems" @select="printDoc" class="mr-4 order-btn">
                    <fa-icon icon="print"></fa-icon>
                    Распечатать документы
                </dropdown>
                <div class="mr-4 order-btn">
                    <fa-icon icon="comment-dots"></fa-icon>
                    Добавить комментарий
                </div>
                <div class="mr-4 order-btn">
                    <fa-icon icon="plus"></fa-icon>
                    Создать заказ
                </div>
                <div class="mr-4 order-btn">
                    <fa-icon icon="file-download"></fa-icon>
                    Скачать таблицу
                </div>
            </div>
            <div>
                <a :href="getRoute('orders.create')" class="btn btn-dark">Создать заказ</a>
            </div>
        </div>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all-page-orders" v-model="isSelectAllPageOrders" @click="selectAllPageOrders()">
                        <label for="select-all-page-orders" class="mb-0">Все</label>
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
                <tr v-for="order in orders">
                    <td><input type="checkbox" value="true" class="order-select" :value="order.id"></td>
                    <td v-for="column in columns" v-if="column.isShown">
                        <order-status v-if="column.code === 'status'" :status='order.status'/>
                        <div v-else v-html="column.value(order)"></div>
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="pager.pages !== 1"
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

import Service from '../../../../../scripts/services/services';
import withQuery from 'with-query';
import qs from 'qs';

import { mapGetters } from 'vuex';

import FInput from '../../../../components/filter/f-input.vue';
import FDate from '../../../../components/filter/f-date.vue';
import FSelect from '../../../../components/filter/f-select.vue';
import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
import Dropdown from '../../../../components/dropdown/dropdown.vue';
import Helpers from '../../../../../scripts/helpers';
import ModalColumns from '../../../../components/modal-columns/modal-columns.vue';

import modalMixin from '../../../../mixins/modal.js';

const cleanHiddenFilter = {
    number: '',
    brands: [],
    payType: [],
};

const cleanFilter = Object.assign({
    created: '',
    deliveryTime: '',
    orderStatus: [],
    deliveryStore: [],
    deliveryCity: [],
    deliveryMethod: [],
}, cleanHiddenFilter);

const serverKeys = [
    'createdAtFrom', 'createdAtTo', 'orderStatus', 'deliveryTime', 'deliveryCount', 'deliveryMethod', 'number', 'customer'
];

export default {
    name: 'page-order-flow',
    mixins: [modalMixin],
    props: [
        'iOrders',
        'iCurrentPage',
        'iPager',
        'orderStatuses',
        'deliveryStores',
        'deliveryCities',
        'deliveryMethods',
        'paymentMethods',
        'iFilter',
        'iSort',
        'brands'
    ],
    components: {
        FInput,
        FDate,
        FSelect,
        FMultiSelect,
        Dropdown,
        ModalColumns,
    },
    data() {
        let self = this;
        let filter = Object.assign({}, cleanFilter, this.iFilter);
        filter.orderStatus = filter.orderStatus.map(value => parseInt(value));
        filter.deliveryStore = filter.deliveryStore.map(value => parseInt(value));
        filter.deliveryCity = filter.deliveryCity.map(value => parseInt(value));
        filter.deliveryMethod = filter.deliveryMethod.map(value => parseInt(value));
        filter.payType = filter.payType.map(value => parseInt(value));
        filter.brands = filter.brands.map(value => parseInt(value));
        return {
            opened: false,
            currentPage: this.iCurrentPage,
            orders: this.iOrders,
            filter,
            sort: this.iSort,
            appliedFilter: {},
            pager: this.iPager,
            isSelectAllPageOrders: false,
            columns: [
                {
                    name: '№ заказа',
                    code: 'number',
                    value: function(order) {
                        return '<a href="' + self.getRoute('orders.flowDetail', {id: order.id}) + '">' +
                            order.number + '</a>';
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Дата заказа',
                    code: 'created_at',
                    value: function(order) {
                        return order.created_at;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Дата последнего изменения',
                    code: 'status_at',
                    value: function(order) {
                        return order.status_at;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Дата доставки',
                    code: 'delivery_at',
                    value: function(order) {
                        return order.deliveries[0] ?
                            order.deliveries[0].delivery_at :
                            'Нет данных';
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Сумма',
                    code: 'cost',
                    value: function(order) {
                        return self.preparePrice(order.cost);
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },

                {
                    name: 'Покупатель',
                    code: 'customer',
                    value: function(order) {
                        return order.customer ?
                            `${order.customer.last_name} ${order.customer.first_name} ${order.customer.middle_name}` :
                            'Нет данных'
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Город доставки',
                    code: 'delivery_city',
                    value: function(order) {
                        return order.delivery_city.name; //todo
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Статус',
                    code: 'status',
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Кол-во доставок',
                    code: 'delivery_qty',
                    value: function(order) {
                        return order.deliveries.length;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Комментарий',
                    code: 'comment',
                    value: function(order) {
                        return order.comment;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
            ],
            dropdownItems: [
                {value: 1, text: 'Все'},
                {value: 2, text: 'Покупателю'},
                {value: 3, text: 'Курьеру'},
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
            Service.net().get(this.route('orders.FlowPagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
                sort: this.sort,
            }).then(data => {
                this.orders = data.orders;
                if (data.pager) {
                    this.pager = data.pager
                }
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
        selectAllPageOrders() {
            let checkboxes = document.getElementsByClassName('order-select');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = this.isSelectAllPageOrders ? '' : 'checked';
            }
        },
        showChangeColumns() {
            this.openModal('list_columns');
        },
        downloadDoc(id) {
            window.open('/manual/docs.pdf');
        },
        printDoc(id) {
            let iframe = this._printIframe;
            if (!this._printIframe) {
                iframe = this._printIframe = document.createElement('iframe');
                document.body.appendChild(iframe);

                iframe.style.display = 'none';
                iframe.onload = function() {
                    setTimeout(function() {
                        iframe.focus();
                        iframe.contentWindow.print();
                    }, 1);
                };
            }

            iframe.src = '/manual/docs.pdf';
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        statusOptions() {
            return Object.values(this.orderStatuses).map(status => ({
                value: status.id,
                text: status.name
            }));
        },
        deliveryStoreOptions() {
            return Object.values(this.deliveryStores).map(store => ({value: store.id, text: store.name}));
        },
        deliveryCityOptions() {
            return Object.values(this.deliveryCities).map(city => ({value: city.id, text: city.name}));
        },
        deliveryMethodOptions() {
            return Object.values(this.deliveryMethods).map(method => ({value: method.id, text: method.name}));
        },
        brandOptions() {
            return this.brands.map(brand => ({value: brand.id, text: brand.name}));
        },
        paymentMethodOptions() {
            return Object.values(this.paymentMethods).map(method => ({value: method.id, text: method.name}));
        },
        editedShowColumns() {
            return this.columns.filter(function(column) {
                return !column.isAlwaysShown;
            })
        }
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
    .action-bar {
        padding: 10px 20px;
    }
    .order-btn {
        cursor: pointer;
    }
</style>
