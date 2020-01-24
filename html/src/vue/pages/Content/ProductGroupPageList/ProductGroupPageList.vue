<template>
    <layout-main>
        <div class="mt-3 mb-3 shadow p-3">
            <div class="row">
                <f-input v-model="filter.id" class="col-lg-3 col-md-6 col-sm-12">ID</f-input>
                <f-select
                        v-model="filter.type"
                        :options="typeOptions"
                        class="col-lg-3 col-md-6 col-sm-12"
                >Тип</f-select>
            </div>
            <button @click="applyFilter" class="btn btn-dark">Применить</button>
            <button @click="clearFilter" class="btn btn-secondary">Очистить</button>
        </div>
        <div class="mb-3">
            Всего подборок: {{ pager.total }}.
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th></th>
                    <th class="with-small">Название</th>
                    <th>Тип</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="productGroupPage in productGroupPages">
                    <td>{{productGroupPage.id}}</td>
                    <td><img :src="productGroupPage.photo ? productGroupPage.photo : '//placehold.it/75x50?text=No+image'" class="preview"></td>
                    <td class="with-small">
                        <a :href="getRoute('productGroupPage.detail', {id: productGroupPage.id})">{{productGroupPage.name}}</a>
                    </td>
                    <td>{{productGroupPage.type.name}}</td>
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
        type: '',
    };

    export default {
        components: {
            FSelect,
            FInput,
        },
        props: {
            iProductGroupPages: {},
            iPager: {},
            iCurrentPage: {},
            iFilter: {},
            options: {}
        },
        data() {
            let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
            return {
                productGroupPages: this.iProductGroupPages,
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
                Services.net().get(this.route('productGroupPages.listPage'), {
                    page: this.currentPage,
                    filter: this.filter,
                    //sort: this.sort,
                }).then(data => {
                    this.productGroupPages = data.productGroupPages;
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
            typeOptions() {
                return this.options.types.map(type => ({value: type.id, text: type.name}));
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
