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
                <f-input v-model="filter.number" class="col-sm-12 col-md-3 col-xl-2">
                    № заказа
                </f-input>
                <f-input v-model="filter.customer" class="col-sm-12 col-md-3 col-xl-5">
                    ФИО, e-mail или телефон покупателя
                </f-input>
                <f-date v-if="!filter.use_period"
                        v-model="filter.created_at"
                        @change="filter.created_between = []"
                        class="col-sm-12 col-md-3 col-xl-3">
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               v-model="filter.use_period"
                               class="custom-control-input"
                               id="created_at">
                        <label class="custom-control-label" for="created_at">Дата заказа</label>
                    </div>
                </f-date>
                <f-date v-else
                        v-model="filter.created_between"
                        @change="filter.created_at = []"
                        class="col-sm-12 col-md-3 col-xl-3"
                        range confirm>
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               v-model="filter.use_period"
                               class="custom-control-input"
                               id="created_between">
                        <label class="custom-control-label" for="created_between">Период заказа</label>
                    </div>
                </f-date>
                <f-multi-select v-model="filter.status" :options="statusOptions" class="col-sm-12 col-md-3 col-xl-2">
                    Статус
                </f-multi-select>
            </div>
            <transition name="slide">
                <div v-if="opened">
                    <div class="additional-filter pt-3 mt-3">
                        <div class="row">
                            <f-input v-model="filter.price_from" type="number" class="col-sm-12 col-md-3">
                                Сумма заказа
                                <template #prepend><span class="input-group-text">от</span></template>
                                <template #append><span class="input-group-text">руб.</span></template>
                            </f-input>
                            <f-input v-model="filter.price_to" type="number" class="col-sm-12 col-md-3">
                                &nbsp;
                                <template #prepend><span class="input-group-text">до</span></template>
                                <template #append><span class="input-group-text">руб.</span></template>
                            </f-input>
                            <f-input v-model="filter.offer_xml_id" type="text" class="col-sm-12 col-md-3">
                                Код товара из ERP
                                <template #help>Код из внешней системы, по которому импортируется товар</template>
                            </f-input>
                            <f-input v-model="filter.product_vendor_code" type="text" class="col-sm-12 col-md-3">
                                Артикул
                                <template #help>Артикул товара в системе iBT</template>
                            </f-input>
                        </div>
                        <div class="row">
                            <f-multi-select v-model="filter.brands" :options="brandOptions" class="col-sm-12 col-md-3">
                                Бренд
                                <template #help>Будут показаны заказы в которых есть товары указанного бренда</template>
                            </f-multi-select>
                            <f-multi-select v-model="filter.merchants" :options="merchantOptions" class="col-sm-12 col-md-3">
                                Мерчант
                                <template #help>Будут показаны заказы в которых есть товары указанного мерчанта</template>
                            </f-multi-select>
                            <f-multi-select v-model="filter.payment_method" :options="paymentMethodOptions" class="col-sm-12 col-md-3">
                                Способ оплаты
                            </f-multi-select>
                            <f-multi-select v-model="filter.stores" :options="storeOptions" class="col-sm-12 col-md-3">
                                Склад отгрузки
                            </f-multi-select>
                        </div>
                        <div class="row">
                            <f-multi-select v-model="filter.delivery_type" :options="deliveryTypeOptions" class="col-sm-12 col-md-2">
                                Тип доставки
                            </f-multi-select>
                            <f-multi-select v-model="filter.delivery_service" :options="deliveryServiceOptions" class="col-sm-12 col-md-2">
                                Логистический оператор
                            </f-multi-select>
                            <v-dadata
                                    :value.sync="filter.delivery_city_string"
                                    bounds="city-settlement"
                                    @onSelect="onDeliveryCitySelect"
                                    class="col-sm-12 col-md-4"
                            >Город доставки</v-dadata>
                            <f-date v-model="filter.psd" class="col-sm-12 col-md-2" range confirm>
                                PSD
                            </f-date>
                            <f-date v-model="filter.pdd" class="col-sm-12 col-md-2" range confirm>
                                PDD
                            </f-date>
                        </div>
                        <div class="row">
                            <f-select v-model="filter.is_canceled" :options="booleanOptions" class="col-sm-12 col-md-2">
                                Отменен
                            </f-select>
                            <f-select v-model="filter.is_problem" :options="booleanOptions" class="col-sm-12 col-md-2">
                                Проблемный
                            </f-select>
                            <f-select v-model="filter.is_require_check" :options="booleanOptions" class="col-sm-12 col-md-2">
                                Требует проверки
                            </f-select>
                            <f-multi-select v-model="filter.confirmation_type" :options="confirmationTypeOptions" class="col-sm-12 col-md-2">
                                Тип подтверждения
                            </f-multi-select>
                            <f-input v-model="filter.manager_comment" class="col-sm-12 col-md-4">
                                Комментарий менеджера
                            </f-input>
                        </div>
                        <div class="row">
                            <f-multi-select v-model="filter.type" :options="typeOptions" class="col-sm-12 col-md-2">
                                Тип заказа
                            </f-multi-select>
                          <f-input v-model="filter.delivery_xml_id" type="text" class="col-sm-12 col-md-3">
                            Трек номер ЛО
                          </f-input>
                        </div>
                    </div>
                </div>
            </transition>
            <template v-slot:footer>
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего заказов: {{ pager.total }}.</span>
            </template>
        </b-card>
        <div class="d-flex justify-content-between mt-3 mb-3" v-if="canUpdate(blocks.orders)">
            <div>
                <a :href="getRoute('orders.create')" class="btn btn-success mt-3">Создать заказ</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-page-orders" v-model="isSelectAllPageOrders" @click="selectAllPageOrders()">
                            <label for="select-all-page-orders" class="mb-0">Все</label>
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
                    <tr v-for="order in orders">
                        <td><input type="checkbox" value="true" class="order-select" :value="order.id"></td>
                        <td v-for="column in columns" v-if="column.isShown">
                            <template v-if="column.code === 'number'">
                                <a :href="getRoute('orders.detail', {id: order.id})">{{order.number}}</a><br>
                                <order-type :type='order.type'/>
                            </template>
                            <template v-else-if="column.code === 'status'">
                                <order-status :status='order.status'/>
                                <template v-if="order.is_canceled">
                                    <br><span class="badge badge-danger">Отменен</span>
                                </template>
                                <template v-if="order.is_problem">
                                    <br><span class="badge badge-danger">Проблемный</span>
                                </template>
                                <template v-if="order.is_require_check">
                                    <br><span class="badge badge-danger">Требует проверки</span>
                                </template>
                            </template>
                            <payment-status v-else-if="column.code === 'payment_status'" :status="order.payment_status"></payment-status>
                            <div v-else v-html="column.value(order)"></div>
                        </td>
                        <td></td>
                    </tr>
                    <tr v-if="!orders.length">
                        <td :colspan="columns.length + 1">Заказов нет</td>
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
    import Helpers from '../../../../scripts/helpers';
    import ModalColumns from '../../../components/modal-columns/modal-columns.vue';

    import modalMixin from '../../../mixins/modal.js';

    const cleanHiddenFilter = {
        price_from: '',
        price_to: '',
        offer_xml_id: '',
        product_vendor_code: '',
        delivery_xml_id: '',
        brands: [],
        merchants: [],
        payment_method: [],
        stores: [],
        delivery_type: [],
        delivery_service: [],
        delivery_city: '',
        delivery_city_string: '',
        psd: [],
        pdd: [],
        is_canceled: 0,
        is_problem: 0,
        is_require_check: 0,
        confirmation_type: [],
        manager_comment: '',
        type: [],
    };

    const cleanFilter = Object.assign({
        number: '',
        customer: '',
        created_at: [],
        created_between: [],
        status: [],
    }, cleanHiddenFilter);

    const serverKeys = [
        'number',
        'customer',
        'created_at',
        'created_between',
        'status',
        'price_from',
        'price_to',
        'offer_xml_id',
        'product_vendor_code',
        'delivery_xml_id',
        'brands',
        'merchants',
        'payment_method',
        'stores',
        'delivery_type',
        'delivery_service',
        'delivery_city',
        'delivery_city_string',
        'psd',
        'pdd',
        'is_canceled',
        'is_problem',
        'is_require_check',
        'confirmation_type',
        'manager_comment',
        'type',
    ];

    export default {
        mixins: [modalMixin],
        props: [
            'iOrders',
            'iCurrentPage',
            'iPager',
            'merchants',
            'confirmationTypes',
            'paymentMethods',
            'iFilter',
            'iSort',
            'brands',
            'stores',
            'orderTypes',
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
            filter.payment_method = filter.payment_method.map(value => parseInt(value));
            filter.delivery_service = filter.delivery_service.map(value => parseInt(value));
            filter.brands = filter.brands.map(value => parseInt(value));
            filter.merchants = filter.merchants.map(value => parseInt(value));
            filter.confirmation_type = filter.confirmation_type.map(value => parseInt(value));
            filter.type = filter.type.map(value => parseInt(value));
            filter.stores = filter.stores.map(value => parseInt(value));
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
                        name: 'Статус',
                        code: 'status',
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Тип подтверждения',
                        code: 'confirmation_type',
                        value: function(order) {
                            return order.confirmation_type.name;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Клиент',
                        code: 'customer',
                        value: function(order) {
                            return order.customer ? order.customer.full_name + '<br><a href="tel:' + order.customer.phone + '" target="_blank">' + order.customer.phone + '</a>': 'N/A';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    /*{
                        name: 'RFM-сегмент клиента',
                        code: 'customer_segment',
                        value: function(order) {
                            return '-';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },*/
                    {
                        name: 'Стоимость товаров',
                        code: 'price_products',
                        value: function(order) {
                            return self.preparePrice(order.product_price) + ' руб.';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Стоимость доставки',
                        code: 'price_delivery',
                        value: function(order) {
                            return self.preparePrice(order.delivery_price) + ' руб.';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Стоимость заказа',
                        code: 'price',
                        value: function(order) {
                            return self.preparePrice(order.price) + ' руб.';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Способ оплаты',
                        code: 'payment_methods',
                        value: function(order) {
                            return order.payment_methods;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Статус оплаты',
                        code: 'payment_status',
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Логистический оператор',
                        description: 'Логистический оператор на последней миле',
                        code: 'delivery_services',
                        value: function(order) {
                            return order.delivery_services;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Город доставки',
                        code: 'delivery_cities',
                        value: function(order) {
                            return order.delivery_cities;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Дата доставки',
                        code: 'delivery_dates',
                        value: function(order) {
                            return order.delivery_dates;
                        },
                        isShown: false,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Кол-во отправлений',
                        code: 'shipments_qty',
                        value: function(order) {
                            return order.shipments_qty;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'PSD',
                        description: 'По последнему отправлению последней доставки',
                        code: 'psd_last',
                        value: function(order) {
                            return order.psd_last;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'PDD',
                        description: 'По последней доставки',
                        code: 'pdd_last',
                        value: function(order) {
                            return order.pdd_last;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    /*{
                        name: 'Последнее изменение',
                        code: 'last_updated_at',
                        value: function(order) {
                            if (order.latestHistory) {
                                let last_updated_at = order.latestHistory.user ?
                                    order.latestHistory.user.full_name : 'Система';
                                return last_updated_at + '<br>' + order.latestHistory.updated_at;
                            } else {
                                return '';
                            }
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },*/
                    {
                        name: 'Источник заказа',
                        code: 'sources',
                        value: function(order) {
                            let sources = [];
                            if (order.sources) {
                                order.sources.forEach(function (item) {
                                    sources.push((item.user ? item.user.full_name + ' ' : '') + (item.promo_code ? item.promo_code : ''));
                                });
                            }

                            return sources.join('<br>');
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
                Services.net().get(this.route('orders.pagination'), {
                    page: this.currentPage,
                    filter: this.appliedFilter,
                    sort: this.sort,
                }).then(data => {
                    this.orders = data.orders;
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
            onDeliveryCitySelect(suggestion) {
                let address = suggestion.data;

                this.filter.delivery_city = address.settlement_fias_id ? address.settlement_fias_id :
                    address.city_fias_id;
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
            statusOptions() {
                return Object.values(this.orderStatuses).map(status => ({
                    value: status.id,
                    text: status.name
                }));
            },
            deliveryTypeOptions() {
                return Object.values(this.deliveryTypes).map(type => ({value: type.id, text: type.name}));
            },
            brandOptions() {
                return this.brands.map(brand => ({value: brand.id, text: brand.name}));
            },
            paymentMethodOptions() {
                return Object.values(this.paymentMethods).map(method => ({value: method.id, text: method.name}));
            },
            deliveryServiceOptions() {
                return Object.values(this.deliveryServices).map(service => ({value: service.id, text: service.name}));
            },
            merchantOptions() {
                return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.legal_name}));
            },
            confirmationTypeOptions() {
                return Object.values(this.confirmationTypes).map(confirmation_type => ({value: confirmation_type.id, text: confirmation_type.name}));
            },
            storeOptions() {
                return Object.values(this.stores).map(store => ({value: store.id, text: store.address.address_string}));
            },
            booleanOptions() {
                return [{value: 0, text: 'Нет'}, {value: 1, text: 'Да'}];
            },
            typeOptions() {
                return Object.values(this.orderTypes).map(type => ({value: type.id, text: type.name}))
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
</style>
