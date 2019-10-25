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
            Всего товаров: {{ pager.total }}.
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th></th>
                    <th class="with-small">Название <small>Артикул</small></th>
                    <th>Бренд</th>
                    <th>Категория</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in products">
                    <td>{{product.id}}</td>
                    <td><img :src="product.photo ? product.photo : '//placehold.it/75x50?text=No+image'" class="preview"></td>
                    <td class="with-small">
                        {{product.name}}
                        <small>{{product.vendor_code}}</small>
                    </td>
                    <td>{{product.brand.name}}</td>
                    <td>{{product.category.name}}</td>
                </tr>
            </tbody>
        </table>
        <div>
            <b-pagination
                    v-if="pager.pages !== 1"
                    v-model="currentPage"
                    :total-rows="pager.total"
                    :per-page="pager.pageSize"
                    @change="changePage"
                    :hide-goto-end-buttons="pager.pages < 10"
                    class="mt-3 float-right"
            ></b-pagination>
        </div>
    </layout-main>
</template>

<script>

    import Services from "../../../../scripts/services/services";
    import withQuery from "with-query";

    import FSelect from "../../../components/filter/f-select.vue";
    import FInput from "../../../components/filter/f-input.vue";
    import {mapGetters} from "vuex";

    const cleanFilter = {
        id: '',
        vendorCode: '',
        brand: '',
        category: ''
    };

    export default {
        components: {
            FSelect,
            FInput,
        },
        props: {
            iProducts: {},
            iPager: {},
            iCurrentPage: {},
            iFilter: {},
            options: {}
        },
        data() {
            let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
            return {
                products: this.iProducts,
                pager: this.iPager,
                currentPage: this.iCurrentPage || 1,
                filter,
            };
        },
        methods: {
            changePage(newPage) {
                let cleanFilter = {};
                for (let [key, value] of Object.entries(this.filter)) {
                    if (value) {
                        cleanFilter[key] = value;
                    }
                }
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: newPage,
                    filter: cleanFilter,
                    //sort: this.sort
                }));
            },
            loadPage() {
                Services.net().get(this.route('products.listPage'), {
                    page: this.currentPage,
                    filter: this.filter,
                    //sort: this.sort,
                }).then(data => {
                    this.products = data.products;
                    if (data.pager) {
                        this.pager = data.pager
                    }
                });
            },
            applyFilter() {
                this.changePage(1);
                this.loadPage();
            },
            clearFilter() {
                this.$set(this, 'filter', JSON.parse(JSON.stringify(cleanFilter)));
                this.applyFilter();
            },

        },
        created() {
            window.onpopstate = () => {
                let query = qs.parse(document.location.search.substr(1));
                if (query.page) {
                    this.currentPage = query.page;
                }
            };
        },
        watch: {
            currentPage() {
                this.loadPage();
            }
        },
        computed: {
            ...mapGetters(['getRoute']),
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
