<template>
    <layout-main>
        <div class="mt-3 mb-3 shadow p-3">
            <div class="row">
                <f-input v-model="filter.id" class="col-lg-3 col-md-6 col-sm-12">ID</f-input>
                <f-input v-model="filter.vendorCode" class="col-lg-3 col-md-6 col-sm-12">Артикул</f-input>
                <f-select
                        v-model="filter.brand"
                        :options="brandOptions"
                        class="col-lg-3 col-md-6 col-sm-12"
                >Бренд</f-select>
                <f-select
                        v-model="filter.category"
                        :options="categoryOptions"
                        class="col-lg-3 col-md-6 col-sm-12"
                >Категория</f-select>
            </div>
            <button @click="applyFilter" class="btn btn-dark">Применить</button>
            <button @click="clearFilter" class="btn btn-secondary">Очистить</button>
        </div>
        <div class="mb-3">
            Всего товаров: {{ total }}.
        </div>
        <table class="table">
            <thead>
                <tr>
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

    const cleanFilter = {
        id: '',
        vendorCode: '',
        brand: '',
        category: ''
    };
    export default {
        mixins: [mediaMixin],
        components: {
            FSelect,
            FInput,
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
            return {
                filter,
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
            }
        },
        created() {
            window.onpopstate = () => {
                let query = qs.parse(document.location.search.substr(1));
                if (query.page) {
                    this.page = query.page;
                }
            };
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
                return this.options.brands.map(brand => ({value: brand.id, text: brand.name}));
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
                        value: category.id,
                        text: category.name,
                        group: groupNames.get(category.parent_id)
                    }));

            },
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
</style>
