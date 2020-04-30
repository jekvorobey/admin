<template>
    <layout-main>
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
                    <f-input v-model="filter.number" class="col-2">
                        № заказа
                    </f-input>
                    <f-input v-model="filter.customer" class="col">
                        ФИО, e-mail или телефон покупателя
                    </f-input>
                    <f-date v-model="filter.created_at" class="col" range confirm>
                        Дата заказа
                    </f-date>
                    <f-multi-select v-model="filter.status" :options="statusOptions" class="col">
                        Статус
                    </f-multi-select>
                </div>

                <div>

                </div>
                <transition name="slide">
                    <div v-if="opened">
                        <div class="additional-filter pt-3 mt-3">
                            <div class="row">
                                <f-input v-model="filter.price_from" type="number" class="col-2">
                                    Сумма заказа
                                    <template #prepend><span class="input-group-text">от</span></template>
                                    <template #append><span class="input-group-text">руб.</span></template>
                                </f-input>
                                <f-input v-model="filter.price_to" type="number" class="col-2">
                                    &nbsp;
                                    <template #prepend><span class="input-group-text">до</span></template>
                                    <template #append><span class="input-group-text">руб.</span></template>
                                </f-input>
                                <f-input v-model="filter.offer_xml_id" type="text" class="col-2">
                                    Код товара из ERP
                                    <template #help>Код из внешней системы, по которому импортируется товар</template>
                                </f-input>
                                <f-input v-model="filter.product_vendor_code" type="text" class="col-2">
                                    Артикул
                                    <template #help>Артикул товара в системе iBT</template>
                                </f-input>
                            </div>
                            <div class="row">
                                <f-multi-select v-model="filter.brands" :options="brandOptions" class="col-4">
                                    Бренд
                                    <template #help>Будут показаны заказы в которых есть товары указанного бренда</template>
                                </f-multi-select>
                                <f-multi-select v-model="filter.payment_method" :options="paymentMethodOptions" class="col-3">
                                    Способ оплаты
                                </f-multi-select>
                                <f-multi-select v-model="filter.delivery_type" :options="deliveryTypeOptions" class="col">
                                    Тип доставки
                                </f-multi-select>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего заказов: {{ pager.total }}.</span>
            </div>
        </div>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <div>
                <a :href="getRoute('orders.create')" class="btn btn-success mt-3">Создать заказ</a>
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
                        <template v-if="column.code === 'status'">
                            <order-status :status='order.status'/>
                            <template v-if="order.is_canceled">
                                <br><span class="badge badge-danger">Отменен</span>
                            </template>
                            <template v-if="order.is_problem">
                                <br><span class="badge badge-danger">Проблемный</span>
                            </template>
                        </template>
                        <payment-status v-else-if="column.code === 'payment_status'" :status="order.payment_status"></payment-status>
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
    import Services from '../../../../scripts/services/services';
    import withQuery from 'with-query';
    import qs from 'qs';
    import {mapGetters} from 'vuex';

    import FInput from '../../../components/filter/f-input.vue';
    import FDate from '../../../components/filter/f-date.vue';
    import FMultiSelect from '../../../components/filter/f-multi-select.vue';
    import Dropdown from '../../../components/dropdown/dropdown.vue';
    import Helpers from '../../../../scripts/helpers';
    import ModalColumns from '../../../components/modal-columns/modal-columns.vue';

    import modalMixin from '../../../mixins/modal.js';

    const cleanHiddenFilter = {
        price_from: '',
        price_to: '',
        offer_xml_id: '',
        product_vendor_code: '',
        brands: [],
        payment_method: [],
        delivery_type: [],
    };

    const cleanFilter = Object.assign({
        number: '',
        customer: '',
        created_at: [],
        status: [],
    }, cleanHiddenFilter);

    const serverKeys = [
        'number',
        'customer',
        'created_at',
        'status',
        'price_from',
        'price_to',
        'offer_xml_id',
        'product_vendor_code',
        'brands',
        'payment_method',
        'delivery_type',
    ];

    export default {
        mixins: [modalMixin],
        props: [
            'iOrders',
            'iCurrentPage',
            'iPager',
            'orderStatuses',
            'deliveryTypes',
            'paymentMethods',
            'iFilter',
            'iSort',
            'brands'
        ],
        components: {
            FInput,
            FDate,
            FMultiSelect,
            Dropdown,
            ModalColumns,
        },
        data() {
            let self = this;
            let filter = Object.assign({}, cleanFilter, this.iFilter);
            filter.status = filter.status.map(value => parseInt(value));
            filter.delivery_type = filter.delivery_type.map(value => parseInt(value));
            filter.payment_method = filter.payment_method.map(value => parseInt(value));
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
                            return '<a href="' + self.getRoute('orders.detail', {id: order.id}) + '">' +
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
                        code: 'delivery_dates',
                        value: function(order) {
                            return order.delivery_dates;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Сумма',
                        code: 'price',
                        value: function(order) {
                            return self.preparePrice(order.price);
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },

                    {
                        name: 'Покупатель',
                        code: 'customer',
                        value: function(order) {
                            return order.customer ? order.customer.full_name : 'N/A';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Город доставки',
                        code: 'cities',
                        value: function(order) {
                            return order.cities;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Статус',
                        code: 'status',
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Статус оплаты',
                        code: 'payment_status',
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
