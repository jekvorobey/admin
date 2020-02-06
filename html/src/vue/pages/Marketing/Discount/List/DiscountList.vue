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
                    <f-input v-model="filter.name" class="col">
                        Название
                    </f-input>
                    <f-input v-model="filter.promo_code" class="col">
                        Промокод
                    </f-input>
                </div>
                <transition name="slide">
                    <div v-if="opened">
                        <div class="additional-filter pt-3 mt-3">
                            <div class="row mt-3">
                                <f-multi-select v-model="filter.type" :options="discountTypesOptions" class="col">
                                    Тип
                                </f-multi-select>
                                <f-multi-select v-model="filter.status" :options="discountStatusesOptions" class="col">
                                    Статус скидки
                                </f-multi-select>
                                <f-multi-select v-model="filter.approval_status" :options="discountApprovalStatusesOptions" class="col">
                                    Статус проверки
                                </f-multi-select>
                            </div>
                            <div class="row mt-3">
                                <f-date v-model="filter.start_date" class="col-2">
                                    Начало действия скидки
                                </f-date>
                                <f-date v-model="filter.end_date" class="col-2">
                                    Конец действия скидки
                                </f-date>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>
        <div>
            <a :href="getRoute('discount.create')" class="btn btn-success mt-3">Создать скидку</a>
        </div>
        <table class="table table-condensed">
            <thead>
            <tr>
                <th>ID</th>
                <th>Тип</th>
                <th>Имя</th>
                <th>Статус проверки</th>
                <th>Статус скидки</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="discounts && Object.keys(discounts).length < 1">
                <td colspan="8" class="text-center">Скидки не найдены!</td>
            </tr>
            <tr v-if="discounts" v-for="(discount, index) in discounts">
                <td>{{ discount.id }}</td>
                <td>{{ discountTypes[discount.type].name }}</td>
                <td>{{ discount.name }}</td>
                <td>
                    <span class="badge" :class="approvalClass(discount)">
                        {{ discount.approvalStatusName }}
                    </span>
                </td>
                <td>
                    <span class="badge" :class="statusClass(discount)">
                        {{ discount.statusName }}
                    </span>
                </td>
                <td>
                    <a :href="getRoute('discount.edit', {id: discount.id})" class="btn btn-info">
                        <fa-icon icon="eye"></fa-icon>
                    </a>
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
    import {mapGetters} from 'vuex';
    import FInput from '../../../../components/filter/f-input.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
    import FDate from '../../../../components/filter/f-date.vue';

    const cleanHiddenFilter = {
    type: [],
    status: [],
    approval_status: [],
    start_date: '',
    end_date: '',
};

const cleanFilter = Object.assign({
    id: '',
    name: '',
    promo_code: '',
}, cleanHiddenFilter);

export default {
    name: 'page-discounts-list',
    components: {
        FInput,
        FMultiSelect,
        FDate,
    },
    props: {
        iDiscounts: [Array, null],
        iCurrentPage: Number,
        discountTypes: Object,
        discountStatuses: Object,
        discountApprovalStatuses: Object,
        pager: Object
    },
    data() {
        return {
            currentPage: this.iCurrentPage,
            discounts: this.iDiscounts,
            filter: {},
            options: [],
            appliedFilter: {},
            opened: false,
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
            Service.net().get(this.route('discount.pagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
            }).then(data => {
                this.discounts = data.iDiscounts;
            });
        },
        approvalClass(discount) {
            switch (discount.approval_status) {
                case 1: return 'badge-dark';
                case 2: return 'badge-info';
                case 3: return 'badge-warning';
                case 4: return 'badge-danger';
                case 5: return 'badge-success';
            }
        },
        statusClass(discount) {
            switch (discount.status) {
                case 1: return 'badge-success';
                case 2: return 'badge-warning';
                case 3: return 'badge-dark';
            }
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
        }
    },
    computed: {
        discountTypesOptions() {
            return Object.values(this.discountTypes).map(type => ({value: type.id, text: type.name}));
        },
        discountStatusesOptions() {
            return Object.values(this.discountStatuses).map(type => ({value: type.id, text: type.name}));
        },
        discountApprovalStatusesOptions() {
            return Object.values(this.discountApprovalStatuses).map(type => ({value: type.id, text: type.name}));
        },

        ...mapGetters(['getRoute'])
    },
    created() {
        window.onpopstate = () => {
            let query = qs.parse(document.location.search.substr(1));
            if (query.page) {
                this.currentPage = query.page;
            }
        };
        // this.opened = this.isHiddenFilterDefaultOpen();
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
