<template>
    <layout-main>
        <div class="card">
            <div class="card-header">
                Фильтр
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.id" class="col-2">
                        ID
                    </f-input>
                    <f-input v-model="filter.name" class="col">
                        Название
                    </f-input>
                    <f-multi-select v-model="filter.status" :options="deliveryServiceStatusOptions" class="col">
                        Статус
                    </f-multi-select>
                </div>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th v-for="column in columns" v-if="column.isShown">
                        {{column.name}}
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
                <tr v-for="deliveryService in deliveryServices">
                    <td v-for="column in columns" v-if="column.isShown" v-html="column.value(deliveryService)"></td>
                    <td></td>
                </tr>
                <tr v-if="!deliveryServices.length">
                    <td :colspan="columns.length">Статусов заказов нет</td>
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
    import Services from '../../../../../scripts/services/services';
    import withQuery from 'with-query';
    import qs from 'qs';
    import ModalColumns from '../../../../components/modal-columns/modal-columns.vue';
    import modalMixin from '../../../../mixins/modal';
    import FInput from '../../../../components/filter/f-input.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';

    const cleanHiddenFilter = {};

const cleanFilter = Object.assign({
    id: '',
    name: '',
    status: [],
}, cleanHiddenFilter);

const serverKeys = [
    'id',
    'name',
    'status',
];

export default {
    props: [
        'iDeliveryServices',
        'iCurrentPage',
        'iPager',
        'iFilter',
        'deliveryServiceStatuses',
    ],
    components: {
        ModalColumns,
        FInput,
        FMultiSelect,
    },
    mixins: [modalMixin],
    data() {
        let self = this;
        let filter = Object.assign({}, cleanFilter, this.iFilter);
        filter.status = filter.status.map(value => parseInt(value));

        return {
            currentPage: this.iCurrentPage,
            filter,
            sort: this.iSort,
            appliedFilter: {},
            deliveryServices: this.iDeliveryServices,
            pager: this.iPager,
            columns: [
                {
                    name: 'ID',
                    code: 'id',
                    value: function(deliveryService) {
                        return '<a href="' + self.getRoute('deliveryService.detail', {id: deliveryService.id}) + '">' +
                            deliveryService.id + '</a>';
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Название',
                    code: 'name',
                    value: function(deliveryService) {
                        return deliveryService.name;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Статус',
                    code: 'status',
                    value: function(deliveryService) {
                        return '<span class="badge ' + self.statusClass(deliveryService.status.id) + '">' + deliveryService.status.name + '</span>';
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Приоритет',
                    code: 'priority',
                    value: function(deliveryService) {
                        return deliveryService.priority;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Приоритет Самовывоз',
                    code: 'pickup_priority',
                    value: function(deliveryService) {
                        return deliveryService.pickup_priority;
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
            Services.net().get(this.route('deliveryService.pagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
                sort: this.sort,
            }).then(data => {
                this.deliveryServices = data.deliveryServices;
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
        showChangeColumns() {
            this.openModal('list_columns');
        },
        statusClass(statusId) {
            switch (statusId) {
                case 1: return 'badge-success';
                case 2: return 'badge-danger';
                case 3: return 'badge-secondary';
                default: return 'badge-light';
            }
        },
    },
    computed: {
        deliveryServiceStatusOptions() {
            return Object.values(this.deliveryServiceStatuses).map(status => ({
                value: status.id,
                text: status.name
            }));
        },
        editedShowColumns() {
            return this.columns.filter(function(column) {
                return !column.isAlwaysShown;
            })
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
        },
    }
};
</script>