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
                    <f-input v-model="filter.id" class="col-2" type="number">
                        ID
                    </f-input>
                    <f-input v-model="filter.name" class="col-4">
                        Название
                    </f-input>
                    <f-multi-select v-model="filter.status"
                                    :options="discountStatusesOptions"
                                    :name="'status'"
                                    class="col-3">
                        Статус
                    </f-multi-select>
                </div>
                <transition name="slide">
                    <div v-show="opened">
                        <div class="additional-filter pt-3 mt-3">
                            <div class="row mt-3">
                                <f-multi-select v-model="filter.type"
                                                :options="discountTypesOptions"
                                                :name="'type'"
                                                class="col-6">
                                    Скидка на
                                </f-multi-select>

                                <v-select v-model="filter.role_id" :options="roles" class="col-6">Роль</v-select>
                            </div>
                            <div class="row mt-3">
                                <f-date v-model="filter.start_date" class="col-3">
                                    Начало действия скидки
                                </f-date>
                                <div class="col-3">
                                    <label>Точная дата
                                        <fa-icon icon="question-circle" v-b-popover.hover="fixDateTooltip"></fa-icon>
                                    </label>
                                    <div>
                                        <input class="ml-5" type="checkbox" v-model="filter.fix_start_date">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <f-date v-model="filter.end_date" class="col-3">
                                    Конец действия скидки
                                </f-date>
                                <div class="col-3">
                                    <label>&nbsp;</label>
                                    <div>
                                        <input class="ml-5" type="checkbox" v-model="filter.fix_end_date">
                                    </div>
                                </div>
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

        <div class="row mb-3">
            <div class="col-12">
                <a :href="getRoute('discount.create')" class="btn btn-success mt-3">Создать скидку</a>
            </div>
        </div>

        <table class="table table-hover">
            <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Скидка</th>
                <th>Срок действия</th>
                <th>Статус</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="discounts && Object.keys(discounts).length < 1">
                <td colspan="8" class="text-center">Скидки не найдены!</td>
            </tr>
            <tr v-if="discounts" v-for="(discount, index) in discounts">
                <td>{{ discount.id }}</td>
                <td>{{ discount.name }}</td>
                <td>{{ discount.value }}{{ discount.value_type === DISCOUNT_VALUE_TYPE_RUB ? ' руб.' : '%' }} на<br/>
                    <b>{{ discountTypeName(discount.type) }}</b>
                </td>
                <td>{{ discount.validityPeriod }}</td>
                <td :class="statusClass(discount)">
                    <span class="badge">{{ discount.statusName }}</span>
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
                v-if="total > iPager.perPage"
                v-model="currentPage"
                :total-rows="total"
                :per-page="iPager.perPage"
                @change="changePage"
        ></b-pagination>
    </layout-main>
</template>

<script>
    import Service from '../../../../../scripts/services/services';
    import withQuery from 'with-query';
    import qs from 'qs';
    import FCheckbox from '../../../../components/filter/f-checkbox.vue';
    import FInput from '../../../../components/filter/f-input.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
    import FDate from '../../../../components/filter/f-date.vue';

    const cleanFilter = {
        id: '',
        name: '',
        type: [],
        status: [],
        start_date: '',
        end_date: '',
        fix_start_date: false,
        fix_end_date: false,
        role_id: null,
    };

    export default {
        name: 'page-discounts-list',
        components: {
            FInput,
            VSelect,
            FMultiSelect,
            FCheckbox,
            FDate,
        },
        props: {
            iDiscounts: [Array, null],
            iCurrentPage: Number,
            discountTypes: Object,
            discountStatuses: Object,
            iPager: Object,
            roles: Array,
            iFilter: Object|Array,
        },
        data() {
            return {
                total: 0,
                currentPage: this.iCurrentPage,
                discounts: this.iDiscounts,
                filter: {},
                options: [],
                appliedFilter: {},
                opened: false,

                // Статус скидки
                STATUS_CREATED: 1,
                STATUS_SENT: 2,
                STATUS_ON_CHECKING: 3,
                STATUS_ACTIVE: 4,
                STATUS_REJECTED: 5,
                STATUS_PAUSED: 6,
                STATUS_EXPIRED: 7,

                // Тип значения
                DISCOUNT_VALUE_TYPE_PERCENT: 1,
                DISCOUNT_VALUE_TYPE_RUB: 2,
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
                    this.total = data.total;
                });
            },
            statusClass(discount) {
                switch (discount.status) {
                    case this.STATUS_ACTIVE:
                        return 'table-success';
                    case this.STATUS_CREATED:
                    case this.STATUS_SENT:
                    case this.STATUS_PAUSED:
                    case this.STATUS_ON_CHECKING:
                        return 'table-warning';
                    case this.STATUS_REJECTED:
                        return 'table-danger';
                    case this.STATUS_EXPIRED:
                        return 'table-secondary';
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
            },
            clearFilter() {
                for (let entry of Object.entries(cleanFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                Service.event().$emit('clear-filter');
                this.applyFilter();
            },
            discountTypeName(type) {
                return (type in this.discountTypes) ? this.discountTypes[type].name : 'N/A';
            },
            initFilter() {
                let fields = [
                    'id',
                    'name',
                    'type',
                    'status',
                    'start_date',
                    'end_date',
                    'fix_start_date',
                    'fix_end_date',
                    'role_id',
                ];

                let filter = {};
                for (let i in fields) {
                    let field = fields[i];
                    if (!(field in this.iFilter)) {
                        continue;
                    }

                    this.opened = this.opened || ['type', 'start_date', 'end_date', 'role_id'].includes(field);

                    switch (field) {
                        case 'id':
                        case 'name':
                        case 'start_date':
                        case 'end_date':
                        case 'fix_start_date':
                        case 'fix_end_date':
                        case 'role_id':
                            filter[field] = this.iFilter[field];
                            break;
                        case 'type':
                        case 'status':
                            let value = this.iFilter[field].map(v => parseInt(v));
                            filter[field] = value;
                            Service.event().$emit('set-filter-' + field, value);
                            break;
                    }
                }

                this.filter = filter;
            },
        },
        computed: {
            discountTypesOptions() {
                return Object.values(this.discountTypes).map(type => ({value: type.id, text: type.name}));
            },
            discountStatusesOptions() {
                return Object.values(this.discountStatuses).map(type => ({value: type.id, text: type.name}));
            },
            fixDateTooltip() {
                return 'Искать точное совпадание с датой начала и/или окончания скидки';
            },
        },
        mounted() {
            this.initFilter();
            this.appliedFilter = this.filter;
        },
        created() {
            this.total = this.iPager.total;
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
