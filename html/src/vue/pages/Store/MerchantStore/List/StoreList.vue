<template>
    <layout-main>
        <div class="card">
            <div class="card-header">
                Фильтр
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.id" class="col">
                        ID
                    </f-input>
                    <f-multi-select v-model="filter.merchant_id" :options="merchantOptions" class="col">
                        Мерчант
                    </f-multi-select>
                    <f-input v-model="filter.name" class="col">
                        Название
                    </f-input>
                    <f-input v-model="filter.address.city" class="col">
                        Населенный пункт
                    </f-input>
                </div>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>
        <div>
            <a :href="getRoute('merchantStore.add')" class="btn btn-success mt-3">Добавить склад</a>
        </div>
        <table class="table table-condensed mt-3">
            <thead>
            <tr>
                <th>ID</th>
                <th>Мерчант</th>
                <th>Название</th>
                <th>Населенный пункт</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="stores && Object.keys(stores).length < 1">
                <td colspan="8" class="text-center">Склады не найдены!</td>
            </tr>
            <tr v-if="stores" v-for="(store, index) in stores">
                <td>{{ store.id }}</td>
                <td>{{ store.merchant ? store.merchant.display_name : '' }}</td>
                <td>
                    <a :href="getRoute('merchantStore.edit', {id: store.id})">{{ store.name }}</a>
                </td>
                <td>{{ store.address ? store.address.city : '' }}</td>
                <td>
                    <fa-icon icon="trash-alt" @click="deleteStore(index)"></fa-icon>
                </td>
            </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="pager.pages > 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                :hide-goto-end-buttons="pager.pages < 10"
                @change="changePage"
        ></b-pagination>
    </layout-main>
</template>

<script>
    import Service from '../../../../../scripts/services/services';
    import withQuery from 'with-query';
    import qs from 'qs';
    import FInput from '../../../../components/filter/f-input.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
    import FDate from '../../../../components/filter/f-date.vue';

    const cleanFilter = {
    id: '',
    merchant_id: [],
    name: '',
    address: {
        city: '',
    }
};

export default {
    name: 'page-stores-list',
    components: {
        FInput,
        FMultiSelect,
        FDate,
    },
    props: {
        iStores: [Array, null],
        iFilter: [Object, {}],
        iCurrentPage: Number,
        pager: Object,
        merchants: [Array]
    },
    data() {
        let filter = Object.assign({}, cleanFilter, this.iFilter);
        filter.merchant_id = filter.merchant_id.map(value => parseInt(value));

        return {
            currentPage: this.iCurrentPage,
            stores: this.iStores,
            filter: filter,
            options: [],
            appliedFilter: {},
        };
    },
    methods: {
        changePage(newPage) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: newPage,
                filter: this.appliedFilter,
            }));
        },
        loadPage() {
            Service.net().get(this.route('merchantStore.pagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
            }).then(data => {
                this.stores = data.iStores;
            });
        },
        applyFilter() {
            let tmpFilter = {};
            for (let [key, value] of Object.entries(this.filter)) {
                if (value && Object.keys(cleanFilter).indexOf(key) !== -1) {
                    tmpFilter[key] = value;
                }
            }
            this.appliedFilter = tmpFilter;
            this.currentPage = 1;
            this.changePage(1);
            this.loadPage();
        },
        deleteStore(index) {
            let id = this.stores[index].id;
            if(id) {
                Service.net().delete(
                    this.getRoute('merchantStore.delete', {id: id}),
                    null,
                    null
                ).then(data => {
                });
            }

            this.stores.splice(index, 1);
        },
        clearFilter() {
            for (let entry of Object.entries(cleanFilter)) {
                this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
            }
            this.applyFilter();
        }
    },
    computed: {
        merchantOptions() {
            return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.display_name}));
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
    }
};
</script>
<style scoped>
    .additional-filter {
        border-top: 1px solid #DFDFDF;
    }
</style>
