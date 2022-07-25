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
                    <f-input v-model="filter.id" class="col-2">
                        ID
                    </f-input>
                    <f-multi-select v-model="filter.status" :options="statusOptions" class="col">
                        Статус заявки
                    </f-multi-select>
                    <f-custom-select @onSelect="onSelect" v-model="filter.merchantId" :options="merchantOptions" class="col">
                        Мерчант
                    </f-custom-select>
                </div>

                <transition name="slide">
                    <div v-if="opened" class="additional-filter pt-3 mt-3">
                        <div class="row">
                            <f-date v-model="filter.created_at"
                                    class="col-6"
                                    @change="filter.created_between = []">
                                Дата создания <b>(Точная)</b>
                            </f-date>
                            <f-date v-model="filter.created_between"
                                    class="col-6"
                                    @change="filter.created_at = []"
                                    range confirm>
                                Дата создания <b>(Период)</b>
                            </f-date>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
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
            <tr v-for="claim in claims">
                <td v-for="column in columns" v-if="column.isShown" v-html="column.value(claim)"></td>
                <td></td>
            </tr>
            <tr v-if="!claims.length">
                <td :colspan="columns.length + 1">Заявок нет</td>
            </tr>
            </tbody>
        </table>
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

    import FInput from '../../../components/filter/f-input.vue';
    import FDate from '../../../components/filter/f-date.vue';
    import FMultiSelect from '../../../components/filter/f-multi-select.vue';
    import FSelect from '../../../components/filter/f-select.vue';
    import FCustomSelect from '../../../components/filter/f-custom-select.vue';
    import ModalColumns from '../../../components/modal-columns/modal-columns.vue';
    import Helpers from '../../../../scripts/helpers';

    import modalMixin from '../../../mixins/modal';

    const cleanHiddenFilter = {
    created_at: [], created_between: [],
};

const cleanFilter = Object.assign({
    id: '',
    merchantId: '',
    status: [],
}, cleanHiddenFilter);

const serverKeys = [
    'id',
    'merchantId',
    'status',
    'created_at',
    'created_between',
];

export default {
    components: {
        FInput,
        FDate,
        FSelect,
        FMultiSelect,
        ModalColumns,
        FCustomSelect
    },
    mixins: [modalMixin],
    props: {
        routePrefix: String,
        iClaims: Array,
        claimStatuses: {},
        merchants: {},
        iPager: {},
        iCurrentPage: Number,
        iFilter: {},
        iSort: {},
    },
    data() {
        let self = this;
        let filter = Object.assign({}, cleanFilter, this.iFilter);
        filter.merchantId = parseInt(filter.merchantId);
        filter.status = filter.status.map(value => parseInt(value));

        return {
            opened: false,
            currentPage: this.iCurrentPage,
            claims: this.iClaims,
            filter,
            sort: this.iSort,
            pager: this.iPager,
            options: [],
            appliedFilter: {},
            columns: [
                {
                    name: 'ID',
                    code: 'id',
                    value: function(claim) {
                        return '<a href="' + self.getRoute(self.routePrefix + '.detail',
                            {id: claim.id}) + '">' +
                            claim.id + '</a>';
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Мерчант',
                    code: 'merchant',
                    value: function(claim) {
                        return claim.merchant.legal_name ? claim.merchant.legal_name : 'N/A';
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Автор',
                    code: 'author',
                    value: function(claim) {
                        return claim.userName ? claim.userName : 'N/A';
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Статус',
                    code: 'status',
                    value: function(claim) {
                        return '<span class="badge ' + self.statusClass(claim.status) + '">' +
                            self.statusName(claim.status) + '</span>';
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Кол-во товаров',
                    code: 'products_qty',
                    value: function(claim) {
                        return claim.productsQty;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Дата создания',
                    code: 'date',
                    value: (claim) => {
                        return this.datetimePrint(claim.created_at);
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
            ],
        };
    },
    methods: {
        onSelect(merchantID){
            this.filter.merchantId = merchantID
        },
        statusName(statusId) {
            return this.claimStatuses[statusId] || 'N/A';
        },
        changePage(newPage) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: newPage,
                filter: this.appliedFilter,
                sort: this.sort
            }));
        },
        loadPage() {
            Services.showLoader();
            Services.net().get(this.route(this.routePrefix + '.pagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
                sort: this.sort,
            }).then(data => {
                this.claims = data.items;
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
        showChangeColumns() {
            this.openModal('list_columns');
        },
    },
    computed: {
        statusOptions() {
            let statusOptions = [];
            for (let [key, value] of Object.entries(this.claimStatuses)) {
                statusOptions.push({
                    value: parseInt(key),
                    text: value
                });
            }

            return statusOptions;
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
