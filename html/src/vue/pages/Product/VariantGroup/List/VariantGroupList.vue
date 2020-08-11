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
                <f-input v-model="filter.id" class="col-sm-12 col-md-3">
                    ID
                </f-input>
                <f-multi-select v-model="filter.merchants" :options="merchantOptions" class="col-sm-12 col-md-3">
                    Мерчант
                    <template #help>Будут показаны товарные группы, созданные указанными мерчантами</template>
                </f-multi-select>
                <f-input v-model="filter.offer_xml_id" type="text" class="col-sm-12 col-md-3">
                    Код товара из ERP
                    <template #help>Код из внешней системы, по которому импортируется товар</template>
                </f-input>
                <f-input v-model="filter.product_vendor_code" type="text" class="col-sm-12 col-md-3">
                    Артикул
                    <template #help>Артикул товара в системе iBT</template>
                </f-input>
            </div>
            <transition name="slide">
                <div v-if="opened">
                    <div class="additional-filter pt-3 mt-3">
                        <div class="row">
                            <f-multi-select v-model="filter.brands" :options="brandOptions" class="col-sm-12 col-md-4">
                                Бренд
                                <template #help>Будут показаны товарные группы, в которых есть товары указанного бренда</template>
                            </f-multi-select>
                            <f-multi-select v-model="filter.categories" :options="categoryOptions"
                                    class="col-sm-12 col-md-4">
                                Категория
                                <template #help>Будут показаны товарные группы, в которых есть товары указанной категории</template>
                            </f-multi-select>
                            <f-date v-if="!filter.use_period"
                                    v-model="filter.created_at"
                                    @change="filter.created_between = []"
                                    class="col-sm-12 col-md-4">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox"
                                            v-model="filter.use_period"
                                            class="custom-control-input"
                                            id="created_at">
                                    <label class="custom-control-label" for="created_at">Дата создания</label>
                                </div>
                            </f-date>
                            <f-date v-else
                                    v-model="filter.created_between"
                                    @change="filter.created_at = []"
                                    class="col-sm-12 col-md-4"
                                    range confirm>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox"
                                            v-model="filter.use_period"
                                            class="custom-control-input"
                                            id="created_between">
                                    <label class="custom-control-label" for="created_between">Период дат создания</label>
                                </div>
                            </f-date>
                        </div>
                    </div>
                </div>
            </transition>
            <template v-slot:footer>
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего товарных групп: {{ pager.total }}.</span>
            </template>
        </b-card>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <div>
                <button class="btn btn-success" v-b-modal.modal-add-variant-group>
                    <fa-icon icon="plus"></fa-icon> Создать товарную группу
                </button>
                <modal-add-variant-group :merchant-options="merchantOptions"/>
                <v-delete-button @delete="deleteVariantGroups(selectedVariantGroups)" btn-class="btn-danger"
                        v-if="selectedVariantGroups.length > 0" class="ml-3"/>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th></th>
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
                    <tr v-for="variantGroup in variantGroups">
                        <td><input type="checkbox" v-model="selectedVariantGroups" class="variant-group-select"
                                :value="variantGroup.id"></td>
                        <td v-for="column in columns" v-if="column.isShown">
                            <template v-if="column.code === 'id'">
                                <a :href="getRoute('variantGroups.detail', {id: variantGroup.id})">
                                    {{variantGroup.id}}
                                </a>
                            </template>
                            <template v-else-if="column.code === 'properties'">
                                <p v-for="property in variantGroup.properties">{{property.name}}</p>
                            </template>
                            <template v-else-if="column.code === 'products'">
                                <template v-if="variantGroup.mainProduct">
                                    <b-button v-b-toggle="'products-' + variantGroup.id" variant="primary">
                                        {{variantGroup.mainProduct.name}}
                                    </b-button>
                                    <b-collapse :id="'products-' + variantGroup.id" class="mt-2">
                                        <p v-for="product in variantGroup.products"
                                                :class="product.id === variantGroup.main_product_id ? 'font-weight-bold' : ''">
                                            <a :href="getRoute('products.detail', {id: variantGroup.id})" target="_blank">
                                                {{product.name}}
                                            </a>
                                        </p>
                                    </b-collapse>
                                </template>
                            </template>
                            <div v-else v-html="column.value(variantGroup)"></div>
                        </td>
                        <td>
                            <v-delete-button @delete="deleteVariantGroups([variantGroup.id])" btn-class="btn-danger"/>
                        </td>
                    </tr>
                    <tr v-if="!variantGroups.length">
                        <td :colspan="columns.length + 1">Торговых групп нет</td>
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
    import Services from '../../../../../scripts/services/services';
    import withQuery from 'with-query';
    import qs from 'qs';
    import {mapGetters} from 'vuex';

    import FInput from '../../../../components/filter/f-input.vue';
    import FDate from '../../../../components/filter/f-date.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Helpers from '../../../../../scripts/helpers';
    import ModalColumns from '../../../../components/modal-columns/modal-columns.vue';

    import modalMixin from '../../../../mixins/modal.js';
    import ModalAddVariantGroup from './components/modal-add-variant-group.vue';

    const cleanHiddenFilter = {
        brands: [],
        categories: [],
        created_at: [],
        created_between: [],
    };

    const cleanFilter = Object.assign({
        id: '',
        merchants: [],
        offer_xml_id: '',
        product_vendor_code: '',
    }, cleanHiddenFilter);

    const serverKeys = [
        'id',
        'merchants',
        'offer_xml_id',
        'product_vendor_code',
        'brands',
        'categories',
        'created_at',
        'created_between',
    ];

    export default {
        mixins: [modalMixin],
        props: [
            'iVariantGroups',
            'iCurrentPage',
            'iPager',
            'merchants',
            'iFilter',
            'iSort',
            'brands',
            'categories',
        ],
        components: {
            ModalAddVariantGroup,
            FInput,
            FDate,
            FMultiSelect,
            ModalColumns,
            VDeleteButton,
        },
        data() {
            let filter = Object.assign({}, cleanFilter, this.iFilter);
            filter.brands = filter.brands.map(value => parseInt(value));
            filter.categories = filter.categories.map(value => parseInt(value));

            return {
                opened: false,
                currentPage: this.iCurrentPage,
                variantGroups: this.iVariantGroups,
                filter,
                sort: this.iSort,
                appliedFilter: {},
                pager: this.iPager,
                selectedVariantGroups: [],
                columns: [
                    {
                        name: 'ID',
                        code: 'id',
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Название',
                        code: 'name',
                        isShown: true,
                        isAlwaysShown: true,
                        value: function(variantGroup) {
                            return variantGroup.name;
                        },
                    },
                    {
                        name: 'Мерчант',
                        code: 'name',
                        isShown: true,
                        isAlwaysShown: false,
                        value: function(variantGroup) {
                            return variantGroup.merchant ? variantGroup.merchant.display_name : '';
                        },
                    },
                    {
                        name: 'Характеристики',
                        code: 'properties',
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Товары',
                        code: 'products',
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Дата создания',
                        code: 'created_at',
                        value: function(variantGroup) {
                            return variantGroup.created_at;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Дата изменения',
                        code: 'updated_at',
                        value: function(variantGroup) {
                            return variantGroup.updated_at;
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
                Services.net().get(this.route('variantGroups.pagination'), {
                    page: this.currentPage,
                    filter: this.appliedFilter,
                    sort: this.sort,
                }).then(data => {
                    this.variantGroups = data.variantGroups;
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

            showChangeColumns() {
                this.openModal('list_columns');
            },
            deleteVariantGroups(ids) {
                Services.showLoader();
                Services.net().delete(this.getRoute('variantGroups.delete'), {
                    ids: ids,
                }).then((data) => {
                    Services.msg("Удаление прошло успешно");
                    this.loadPage();
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
            brandOptions() {
                return this.brands.map(brand => ({value: brand.id, text: brand.name}));
            },
            categoryOptions() {
                return this.categories.map(category => ({value: category.id, text: category.name}));
            },
            merchantOptions() {
                return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.legal_name}));
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
