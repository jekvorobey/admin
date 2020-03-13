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
                    <f-input v-model="filter.id" class="col">
                        ID
                    </f-input>
                    <f-date v-model="filter.created_at" class="col-2">
                        Дата создания
                    </f-date>
                    <f-checkbox v-model="filter.showDone" class="mr-lg-3 mr-lg-5">
                        Только готовые
                    </f-checkbox>
                </div>
                <transition name="slide">
                    <div v-if="opened">
                        <div class="additional-filter pt-3 mt-3">

                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>

        <table class="table" v-if="claims && claims.length > 0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Тип</th>
                    <th>Статус</th>
                    <th>Кол-во товаров</th>
                    <th>Автор</th>
                    <th>Создана</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="claim in claims">
                    <td><a :href="getRoute('contentClaims.detail', {id: claim.id})">{{ claim.id }}</a></td>
                    <td>{{ typeName(claim.type) }}</td>
                    <td><span class="badge" :class="statusClass(claim.status)">{{ statusName(claim.status) }}</span></td>
                    <td>{{ claim.payload.productId.length }}</td>
                    <td>{{ claim.userName }}</td>
                    <td>{{ claim.created_at }}</td>
                </tr>
            </tbody>
        </table>
        <p v-else class="text-center p-3">Ничего не найдено!</p>
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

    import withQuery from 'with-query';
    import qs from 'qs';
    import Services from '../../../../../scripts/services/services';
    import FInput from '../../../../components/filter/f-input.vue';
    import FCheckbox from '../../../../components/filter/f-checkbox.vue';
    import FDate from '../../../../components/filter/f-date.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';

    const cleanHiddenFilter = {
    type: [],
    created_at: '',
};

const cleanFilter = Object.assign({
    id: '',
}, cleanHiddenFilter);

export default {
    components: {
        FInput,
        FCheckbox,
        FDate,
        FMultiSelect,
    },
    props: {
        iClaims: Array,
        statuses: {},
        types: {},
        iPager: {},
        iCurrentPage: Number,
        iFilter: {}
    },
    data() {
        return {
            currentPage: this.iCurrentPage,
            claims: this.iClaims,
            pager: this.iPager,
            filter: {},
            options: [],
            appliedFilter: {},
            opened: false,
        };
    },
    methods: {
        statusName(statusId) {
            return this.statuses[statusId] || 'N/A';
        },
        typeName(typeId) {
            return this.types[typeId] || 'N/A';
        },
        changePage(newPage) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: newPage,
                filter: this.filter,
                //sort: this.sort
            }));
        },
        loadPage() {
            Services.net().get(this.route('contentClaims.pagination'), {
                page: this.currentPage,
                filter: this.filter,
                //sort: this.sort,
            }).then(data => {
                this.claims = data.items;
                if (data.pager) {
                    this.pager = data.pager
                }
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
        toggleHiddenFilter() {
            this.opened = !this.opened;
            if (this.opened === false) {
                for (let entry of Object.entries(cleanHiddenFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.applyFilter();
            }
        },
        clearFilter() {
            for (let entry of Object.entries(cleanFilter)) {
                this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
            }
            this.applyFilter();
        },
        statusClass(statusId) {
            switch (statusId) {
                case 1: return 'badge-info';
                case 2: return 'badge-secondary';
                case 3: return 'badge-primary';
                case 4: return 'badge-success';
                case 5: return 'badge-warning';
                default: return 'badge-light';
            }
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
