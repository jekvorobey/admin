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
                <f-input v-model="filter.customer" class="col-sm-12 col-md-3 col-xl-5">
                    ФИО, e-mail или телефон покупателя
                </f-input>
                <f-date v-if="!filter.use_period_for_created_at"
                        v-model="filter.created_at"
                        @change="filter.created_between = []"
                        class="col-sm-12 col-md-3 col-xl-3">
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               v-model="filter.use_period_for_created_at"
                               class="custom-control-input"
                               id="created_at">
                        <label class="custom-control-label" for="created_at">Дата создания корзины</label>
                    </div>
                </f-date>
                <f-date v-else
                        v-model="filter.created_between"
                        @change="filter.created_at = []"
                        class="col-sm-12 col-md-3 col-xl-3"
                        range confirm>
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               v-model="filter.use_period_for_created_at"
                               class="custom-control-input"
                               id="created_between">
                        <label class="custom-control-label" for="created_between">Период создания корзины</label>
                    </div>
                </f-date>
                <f-date v-if="!filter.use_period_for_updated_at"
                        v-model="filter.updated_at"
                        @change="filter.updated_between = []"
                        class="col-sm-12 col-md-3 col-xl-3">
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               v-model="filter.use_period_for_updated_at"
                               class="custom-control-input"
                               id="updated_at">
                        <label class="custom-control-label" for="updated_at">Дата обновления корзины</label>
                    </div>
                </f-date>
                <f-date v-else
                        v-model="filter.updated_between"
                        @change="filter.updated_at = []"
                        class="col-sm-12 col-md-3 col-xl-3"
                        range confirm>
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               v-model="filter.use_period_for_updated_at"
                               class="custom-control-input"
                               id="updated_between">
                        <label class="custom-control-label" for="updated_between">Период обновления корзины</label>
                    </div>
                </f-date>
            </div>
            <transition name="slide">
                <div v-if="opened">
                    <div class="additional-filter pt-3 mt-3">
                        <div class="row">
                            <f-input v-model="filter.price_from" type="number" class="col-sm-12 col-md-3">
                                Сумма корзины
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
                            <f-multi-select v-model="filter.type" :options="typeOptions" class="col-sm-12 col-md-2">
                                Тип корзины
                            </f-multi-select>
                        </div>
                    </div>
                </div>
            </transition>
            <template v-slot:footer>
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего корзин: {{ pager.total }}.</span>
            </template>
        </b-card>
        <div class="table-responsive">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th v-for="column in columns" v-if="column.isShown">
                            <span v-html="column.name"></span>
                            <fa-icon v-if="column.description" icon="question-circle"
                                     v-b-popover.hover="column.description"></fa-icon>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="basket in baskets">
                        <td v-for="column in columns" v-if="column.isShown">
                            <template v-if="column.code === 'number'">
                                <a :href="getRoute('baskets.detail', {id: basket.id})">{{basket.id}}</a><br>
                            </template>
                            <order-type v-else-if="column.code === 'type'" :type='basket.type'/>
                            <div v-else v-html="column.value(basket)"></div>
                        </td>
                    </tr>
                    <tr v-if="!baskets.length">
                        <td :colspan="columns.length + 1">Корзин нет</td>
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
    import Dropdown from '../../../components/dropdown/dropdown.vue';
    import Helpers from '../../../../scripts/helpers';

    const cleanHiddenFilter = {
        price_from: '',
        price_to: '',
        product_vendor_code: '',
        brands: [],
        merchants: [],
        type: [],
    };

    const cleanFilter = Object.assign({
        customer: '',
        created_at: [],
        created_between: [],
        updated_at: [],
        updated_between: [],
    }, cleanHiddenFilter);

    const serverKeys = [
        'customer',
        'created_at',
        'created_between',
        'updated_at',
        'updated_between',
        'price_from',
        'price_to',
        'product_vendor_code',
        'brands',
        'merchants',
        'type',
    ];

    export default {
        props: [
            'iBaskets',
            'iCurrentPage',
            'iPager',
            'merchants',
            'orderTypes',
            'brands',
            'iFilter',
            'iSort',
        ],
        components: {
            FInput,
            FDate,
            FMultiSelect,
            FSelect,
            Dropdown,
        },
        data() {
            let self = this;
            let filter = Object.assign({}, cleanFilter, this.iFilter);
            filter.brands = filter.brands.map(value => parseInt(value));
            filter.merchants = filter.merchants.map(value => parseInt(value));
            filter.type = filter.type.map(value => parseInt(value));
            return {
                opened: false,
                currentPage: this.iCurrentPage,
                baskets: this.iBaskets,
                filter,
                sort: this.iSort,
                appliedFilter: {},
                pager: this.iPager,
                isSelectAllPageOrders: false,
                columns: [
                    {
                        name: '№ корзины',
                        code: 'number',
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Создана',
                        code: 'created_at',
                        value: function(basket) {
                            return basket.created_at;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Обновлена',
                        code: 'updated_at',
                        value: function(basket) {
                            return basket.updated_at;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Клиент',
                        code: 'customer',
                        value: function(basket) {
                            return basket.customer ? basket.customer.full_name + '<br><a href="' + self.getRoute('customers.detail', {id: basket.customer_id}) + '" target="_blank">' + basket.customer.phone + '</a>': 'N/A';
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Сумма корзины',
                        code: 'price_products',
                        value: function(basket) {
                            return basket.price == 0 ? 'N/A' : self.preparePrice(basket.price) + ' руб.';
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Тип корзины',
                        code: 'type',
                        value: function(basket) {
                            return basket.type;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Комментарий менеджера',
                        code: 'manager_comment',
                        value: function(basket) {
                            return basket.manager_comment;
                        },
                        isShown: true,
                        isAlwaysShown: true,
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
                Services.net().get(this.route('baskets.pagination'), {
                    page: this.currentPage,
                    filter: this.appliedFilter,
                    sort: this.sort,
                }).then(data => {
                    this.baskets = data.baskets;
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
        },
        computed: {
            ...mapGetters(['getRoute']),
            brandOptions() {
                return this.brands.map(brand => ({value: brand.id, text: brand.name}));
            },
            merchantOptions() {
                return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.name}));
            },
            booleanOptions() {
                return [
                    {value: true, text: 'Да'},
                    {value: false, text: 'Нет'},
                ];
            },
            typeOptions() {
                return Object.values(this.orderTypes).map(type => ({value: type.id, text: type.name}))
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
