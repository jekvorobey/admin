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
                    <f-input v-model="filter.name" class="col-md-4 col-sm-12">Название</f-input>
                    <f-input v-model="filter.vendorCode" class="col-md-2 col-sm-12">Артикул</f-input>
                    <f-input v-model="filter.id" class="col-md-2 col-sm-12">ID</f-input>
                    <f-select
                            v-model="filter.active"
                            :options="activeOptions"
                            class="col-md-2 col-sm-12"
                    >На витрине</f-select>
                    <f-select
                            v-model="filter.archive"
                            :options="archiveOptions"
                            class="col-md-2 col-sm-12"
                    >Архивность</f-select>
                </div>

                <transition name="slide">
                    <div v-if="opened">
                        <div class="additional-filter pt-3 mt-3">
                            <div class="row">
                                <f-input v-model="filter.priceFrom" class="col-lg-2 col-md-3 col-sm-12">
                                    Цена
                                    <template #prepend><span class="input-group-text">от</span></template>
                                    <template #append><span class="input-group-text">руб.</span></template>
                                </f-input>
                                <f-input v-model="filter.priceTo" class="col-lg-2 col-md-3 col-sm-12">
                                    &nbsp;
                                    <template #prepend><span class="input-group-text">до</span></template>
                                    <template #append><span class="input-group-text">руб.</span></template>
                                </f-input>

                                <f-input v-model="filter.qtyFrom" class="col-lg-2 col-md-3 col-sm-12">
                                    Кол-во
                                    <template #prepend><span class="input-group-text">от</span></template>
                                </f-input>
                                <f-input v-model="filter.qtyTo" class="col-lg-2 col-md-3 col-sm-12">
                                    &nbsp;
                                    <template #prepend><span class="input-group-text">до</span></template>
                                </f-input>

                                <f-date
                                        v-model="filter.dateFrom"
                                        class="col-lg-2 col-md-3 col-sm-12"
                                >Дата содания от</f-date>
                                <f-date
                                        v-model="filter.dateTo"
                                        class="col-lg-2 col-md-3 col-sm-12"
                                >Дата содания до</f-date>
                            </div>
                            <div class="row">
                                <f-select
                                        v-model="filter.brand"
                                        :options="brandOptions"
                                        class="col-md-3 col-sm-12"
                                >Бренд</f-select>
                                <f-select
                                        v-model="filter.category"
                                        :options="categoryOptions"
                                        class="col-md-3 col-sm-12">
                                    Категория
                                    <template #help>Будут показаны товары выбранной и всех дочерних категорий</template>
                                </f-select>
                                <f-select
                                        v-model="filter.approvalStatus"
                                        :options="approvalOptions"
                                        class="col-md-3 col-sm-12"
                                >Согласовано</f-select>
                                <f-select
                                        v-model="filter.productionStatus"
                                        :options="productionOptions"
                                        class="col-md-3 col-sm-12"
                                >Контент</f-select>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего товаров: {{ total }}.</span>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3 mb-3">
            <div v-if="!massEmpty(massProductsType)" class="action-bar d-flex justify-content-start">
                <dropdown :items="statusItems" @select="startChangeStatus" class="mr-4 order-btn">
                    Сменить статус
                </dropdown>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <td></td>
                    <th>ID</th>
                    <th></th>
                    <th class="with-small">Название <small>Артикул</small></th>
                    <th>Бренд</th>
                    <th>Категория</th>
                    <th>Дата создания</th>
                    <th>Стоимость</th>
                    <th>Количество</th>
                    <th>На витрине</th>
                    <th>В архиве</th>
                    <th>Контент</th>
                    <th>Согласование</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in products">
                    <td>
                        <input
                                type="checkbox"
                                :checked="massHas({type:massProductsType, id:product.id})"
                                @change="e => massCheckbox(e, massProductsType, product.id)">
                    </td>
                    <td>{{product.id}}</td>
                    <td><img :src="imageUrl(product.catalogImageId, 100, 100)" class="preview"></td>
                    <td class="with-small">
                        <a :href="getRoute('products.detail', {id: product.id})">{{product.name}}</a>
                        <small>{{product.vendorCode}}</small>
                    </td>
                    <td>{{product.brandName}}</td>
                    <td>{{product.categoryName}}</td>
                    <td>{{formatDate(product.dateAdd)}}</td>
                    <td>{{product.price ? product.price : '--'}}</td>
                    <td>{{product.qty ? product.qty : '--'}}</td>
                    <td>
                        <span class="badge" :class="{'badge-success':product.active,'badge-danger':!product.active}">
                            {{product.active ? 'Да' : 'Нет'}}
                        </span>
                    </td>
                    <td>
                        <span class="badge" :class="{'badge-success':!product.archive,'badge-danger':product.archive}">
                            {{product.archive ? 'Да' : 'Нет'}}
                        </span>
                    </td>
                    <td>
                        <span class="badge" :class="{'badge-success':isProductionDone(product.productionStatus),'badge-danger':!isProductionDone(product.productionStatus)}">
                            {{productionName(product.productionStatus)}}
                        </span>
                    </td>
                    <td>
                        <span class="badge" :class="{'badge-success':isApprovalDone(product.approvalStatus),'badge-danger':!isApprovalDone(product.approvalStatus)}">
                            {{approvalName(product.approvalStatus)}}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        <div>
            <b-pagination
                    v-if="numPages !== 1"
                    v-model="page"
                    :total-rows="total"
                    :per-page="pageSize"
                    :hide-goto-end-buttons="numPages < 10"
                    class="mt-3 float-right"
            ></b-pagination>
        </div>
    </layout-main>
</template>

<script>
    import withQuery from "with-query";

    import FSelect from "../../../components/filter/f-select.vue";
    import FInput from "../../../components/filter/f-input.vue";
    import FDate from "../../../components/filter/f-date.vue";
    import Dropdown from "../../../components/dropdown/dropdown.vue";
    import {mapActions, mapGetters} from "vuex";

    import {
        NAMESPACE,
        GET_PAGE_NUMBER,
        GET_TOTAL,
        GET_PAGE_SIZE,
        GET_NUM_PAGES,
        SET_PAGE,
        ACT_LOAD_PAGE, GET_LIST
    } from "../../../store/modules/products";

    import mediaMixin from '../../../mixins/media';
    import massSelectionMixin from '../../../mixins/mass-selection';
    import Helpers from "../../../../scripts/helpers";

    const TYPE_ARCHIVE = 'archive';
    const TYPE_PRODUCTION = 'production';
    const TYPE_APPROVAL = 'approval';

    const cleanHiddenFilter = {
        active: null,
        archive: null,
        brand: '',
        category: '',
        approvalStatus: '',
        productionStatus: '',
        priceFrom: null,
        priceTo: null,
        qtyFrom: null,
        qtyTo: null,
        dateFrom: null,
        dateTo: null,
    };

    const cleanFilter = Object.assign({
        id: '',
        name: '',
        vendorCode: '',
    }, cleanHiddenFilter);

    export default {
        mixins: [mediaMixin, massSelectionMixin],
        components: {
            FSelect,
            FInput,
            FDate,
            Dropdown,
        },
        props: {
            iProducts: {},
            iTotal: {},
            iCurrentPage: {},
            iFilter: {},
            options: {}
        },
        data() {
            this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
                list: this.iProducts,
                total: this.iTotal,
                page: this.iCurrentPage
            });
            let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
            let statusDropdown = [
                {value: {type: TYPE_ARCHIVE, value: true}, text: 'В архив'},
                {value: {type: TYPE_ARCHIVE, value: false}, text: 'Из архива'},
            ];

            for (let id in this.options.productionStatuses) {
                let status = this.options.productionStatuses[id];
                statusDropdown.push({
                    value: {type: TYPE_PRODUCTION, value: status.id},
                    text: `Контент: ${status.name}`,
                });
            }

            for (let id in this.options.approvalStatuses) {
                let status = this.options.approvalStatuses[id];
                statusDropdown.push({
                    value: {type: TYPE_APPROVAL, value: status.id},
                    text: `Согласование: ${status.name}`,
                });
            }

            return {
                filter,
                opened: false,
                statusItems: statusDropdown,

                massProductsType: 'products',
            };
        },
        methods: {
            ...mapActions(NAMESPACE, [
                ACT_LOAD_PAGE
            ]),
            loadPage(page) {
                let cleanFilter = {};
                for (let [key, value] of Object.entries(this.filter)) {
                    if (value) {
                        cleanFilter[key] = value;
                    }
                }
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: page,
                    filter: cleanFilter,
                }));

                this[ACT_LOAD_PAGE]({
                    page: page,
                    filter: this.filter
                });
            },

            applyFilter() {
                this.loadPage(1);
            },
            clearFilter() {
                this.$set(this, 'filter', JSON.parse(JSON.stringify(cleanFilter)));
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

            approvalName(status) {
                if (this.options.approvalStatuses[status]) {
                    return this.options.approvalStatuses[status].name;
                }
                return 'N/A';
            },
            productionName(status) {
                if (this.options.productionStatuses[status]) {
                    return this.options.productionStatuses[status].name;
                }
                return 'N/A';
            },
            isApprovalDone(status) {
                return status === this.options.approvalDone;
            },
            isProductionDone(status){
                return status === this.options.productionDone;
            },
            formatDate(date) {
                return new Date(date).toLocaleDateString();
            },
            startChangeStatus(action) {
                console.log(action, this.massAll(this.massProductsType));
                switch (action.type) {
                    case TYPE_ARCHIVE:
                        break;
                    case TYPE_PRODUCTION:
                        break;
                    case TYPE_APPROVAL:
                        break;
                }
            }
        },
        created() {
            window.onpopstate = () => {
                let query = qs.parse(document.location.search.substr(1));
                if (query.page) {
                    this.page = query.page;
                }
            };
            this.opened = this.isHiddenFilterDefaultOpen();
        },
        computed: {
            ...mapGetters(['getRoute']),
            ...mapGetters(NAMESPACE, {
                GET_PAGE_NUMBER,
                total: GET_TOTAL,
                pageSize: GET_PAGE_SIZE,
                numPages: GET_NUM_PAGES,
                products: GET_LIST,
            }),
            page: {
                get: function () {
                    return this.GET_PAGE_NUMBER;
                },
                set: function (page) {
                    this.loadPage(page);
                }
            },
            brandOptions() {
                return this.options.brands.map(brand => ({value: brand.code, text: brand.name}));
            },
            categoryOptions() {
                const groups = this.options.categories.filter(category => !category.parent_id);
                const groupNames = new Map();
                for (let group of groups) {
                    groupNames.set(group.id, group.name);
                }
                return this.options.categories
                    .filter(category => category.parent_id)
                    .map(category => ({
                        value: category.code,
                        text: category.name,
                        group: groupNames.get(category.parent_id)
                    }));

            },
            approvalOptions() {
                return Object.values(this.options.approvalStatuses).map(({id, name}) => ({value: id, text: name}));
            },
            productionOptions() {
                return Object.values(this.options.productionStatuses).map(({id, name}) => ({value: id, text: name}));
            },
            archiveOptions() {
                return [
                    {value: true, text: 'В архиве'},
                    {value: false, text: 'Не в архиве'},
                ];
            },
            activeOptions() {
                return [
                    {value: true, text: 'Да'},
                    {value: false, text: 'Нет'},
                ];
            }
        }
    };
</script>

<style scoped>
    th {
        vertical-align: top !important;
    }
    .with-small small{
        display: block;
        color: gray;
        line-height: 1rem;
        overflow: hidden;
    }
    .preview {
        height: 50px;
        border-radius: 5px;
    }
    .additional-filter {
        border-top: 1px solid #DFDFDF;
    }
</style>
