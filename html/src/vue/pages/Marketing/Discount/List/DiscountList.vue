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
                    <f-input v-model="filter.id" class="col-3" type="number">
                        ID
                    </f-input>
                    <f-input v-model="filter.name" class="col-6">
                        Название
                    </f-input>
                    <f-multi-select v-model="filter.status"
                                    :options="discountStatusesOptions"
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

                                <v-select v-model="filter.role_id" :options="roles" class="col-3">Роль</v-select>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <label>Инициатор</label>
                                    <v-select2 v-model="filter.merchant_id"
                                               class="form-control"
                                               :multiple="true"
                                               :selectOnClose="true"
                                               width="100%">
                                        <option v-for="initiator in initiatorsOptions" :value="initiator.value">{{ initiator.text }}</option>
                                    </v-select2>
                                </div>
                                <div class="col-6">
                                    <label>Автор</label>
                                    <v-select2 v-model="filter.user_id"
                                               class="form-control"
                                               :allowClear="true"
                                               :multiple="true"
                                               width="100%">
                                        <option v-for="author in authorsOptions" :value="author.value">{{ author.text }}</option>
                                    </v-select2>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-6">
                                    <div class="row">
                                        <label><b>Дата создания</b></label>
                                    </div>
                                    <div class="row">
                                        <f-date v-model="filter.created_at_from" class="col-6">От</f-date>
                                        <f-date v-model="filter.created_at_to" class="col-6">До</f-date>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="row">
                                        <label><b>Период действия скидки</b></label>
                                    </div>
                                    <div class="row">
                                        <f-date v-model="filter.start_date" class="col-5">От</f-date>
                                        <div class="col-4">
                                            <label>Точная дата
                                                <fa-icon icon="question-circle" v-b-popover.hover="fixDateTooltip"></fa-icon>
                                            </label>
                                            <div>
                                                <input class="ml-5" type="checkbox" v-model="filter.fix_start_date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <f-date v-model="filter.end_date" class="col-5">До</f-date>
                                        <div class="col-4">
                                            <label>Точная дата
                                                <fa-icon icon="question-circle" v-b-popover.hover="fixDateTooltip"></fa-icon>
                                            </label>
                                            <div>
                                                <input class="ml-5" type="checkbox" v-model="filter.fix_end_date">
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <label>Бессрочная</label>
                                            <input class="ml-3 mt-3"
                                                   type="checkbox"
                                                   v-model="filter.indefinitely"
                                                   :checked="iFilter.indefinitely">
                                        </div>
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
            <div class="col-12 mt-3">
                <a v-if="!ifBundle()" :href="getRoute('discount.create')" class="btn btn-success">Создать скидку</a>
                <a v-if="ifBundle()" :href="getRoute('bundle.create')" class="btn btn-success">Создать бандл</a>
                
                <button class="btn btn-danger" :disabled="countSelected < 1" @click="deleteDiscount()">Удалить
                    <template v-if="countSelected <= 1">скидку</template>
                    <template v-else>скидки</template>
                </button>
                <button class="btn btn-secondary" :disabled="countSelected < 1" @click="changeStatus()">Изменить статус
                    <template v-if="countSelected <= 1">скидки</template>
                    <template v-else>скидок</template>
                </button>
                <button class="btn btn-info">Сгенерировать отчет</button>
            </div>
        </div>

        <table class="table table-hover">
            <thead class="thead-light">
            <tr>
                <th><input type="checkbox" v-model="selectAll" @click="changeSelectAll()"></th>
                <th>ID</th>
                <th>Дата создания</th>
                <th>Название</th>
                <th>Скидка</th>
                <th>Период действия</th>
                <th>Инициатор</th>
                <th>Автор</th>
                <th>Статус</th>
                <td><fa-icon icon="cog"/></td>
            </tr>
            </thead>
            <tbody>
            <tr v-if="discounts && Object.keys(discounts).length < 1">
                <td colspan="9" class="text-center">Скидки не найдены!</td>
            </tr>
            <tr v-if="discounts" v-for="(discount, index) in discounts">
                <td><input type="checkbox" v-model="checkboxes[discount.id]"></td>
                <td>{{ discount.id }}</td>
                <td>{{ datePrint(discount.created_at) }}</td>
                <td><a :href="link(discount.id)">{{ discount.name }}</a></td>
                <td>{{ discount.value }}{{ discount.value_type === DISCOUNT_VALUE_TYPE_RUB ? '₽' : '%' }} на<br/>
                    <b>{{ discountTypeName(discount.type) }}</b>
                </td>
                <td>{{ discount.validityPeriod }}</td>
                <td>{{ initiatorName(discount.merchant_id) }}</td>
                <td>{{ userName(discount.user_id) }}</td>
                <td :class="statusClass(discount)">
                    <span class="badge">{{ discount.statusName }}</span>
                </td>
                <td>
                    <a :href="getRoute('discount.detail', {id: discount.id})"
                       class="btn btn-info btn-sm">
                        <fa-icon icon="eye"/>
                    </a>

                    <a :href="getRoute('discount.edit', {id: discount.id})"
                       class="btn btn-success btn-sm mt-1">
                        <fa-icon icon="edit"/>
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

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('UpdateStatusDiscount')">
                <div slot="header">
                    <b>Обновление статуса</b>
                </div>
                <div slot="body">
                    <DiscountList :discounts="selectedDiscounts"></DiscountList>
                    <v-select v-model="newStatus" :options="discountStatusesOptions" class="mt-3">Новый статус</v-select>
                    <button class="btn btn-success mt-3" type="button" @click="approveChangeStatus()" :disabled="processing">Изменить статус</button>
                </div>
            </modal>
        </transition>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('DeleteDiscount')">
                <div slot="header">
                    <b>Вы уверены, что хотите удалить следующие скидки?</b>
                </div>
                <div slot="body">
                    <DiscountList :discounts="selectedDiscounts"></DiscountList>
                    <button class="btn btn-danger mt-3" type="button" @click="approveDelete()" :disabled="processing">Удалить</button>
                </div>
            </modal>
        </transition>
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
    import VSelect2 from '../../../../components/controls/VSelect2/v-select2.vue';
    import DiscountList from '../components/discount-list.vue';
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../../mixins/modal';

    const cleanHiddenFilter = {};
    const cleanFilter = Object.assign({
        id: '',
        name: '',
        type: [],
        status: [],
        user_id: [],
        merchant_id: [],
        created_at_from: '',
        created_at_to: '',
        start_date: '',
        end_date: '',
        fix_start_date: false,
        fix_end_date: false,
        indefinitely: null,
        role_id: null,
    }, cleanHiddenFilter);

    const serverKeys = [
        'id',
        'name',
        'type',
        'status',
        'user_id',
        'merchant_id',
        'created_at_from',
        'created_at_to',
        'start_date',
        'end_date',
        'fix_start_date',
        'fix_end_date',
        'indefinitely',
        'role_id',
    ];

    export default {
        name: 'page-discounts-list',
        components: {
            FInput,
            VSelect,
            FMultiSelect,
            FCheckbox,
            FDate,
            VSelect2,
            DiscountList,
            modal,
        },
        mixins: [modalMixin],
        props: {
            iDiscounts: [Array, null],
            iCurrentPage: Number,
            optionDiscountTypes: Object,
            discountStatuses: Object,
            iPager: Object,
            roles: Array,
            iFilter: Object|Array,
            merchantNames: Object|Array,
            userNames: Object|Array,
            authors: Array,
            initiators: Array,
        },
        data() {
            let filter = Object.assign({}, cleanFilter, this.iFilter);
            filter.status = filter.status.map(value => parseInt(value));
            filter.created_at_from = this.iFilter['created_at'] ? this.iFilter['created_at']['from'] : '';
            filter.created_at_to = this.iFilter['created_at'] ? this.iFilter['created_at']['to'] : '';

            return {
                total: 0,
                currentPage: this.iCurrentPage,
                discounts: this.iDiscounts,
                filter,
                options: [],
                appliedFilter: {},
                opened: false,
                selectAll: false,
                checkboxes: {},
                newStatus: 0,
                processing: false,

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
            link(discountId) {
                return this.getRoute('discount.detail', {id: parseInt(discountId)});
            },
            deleteDiscount() {
                this.openModal('DeleteDiscount');
            },
            approveDelete() {
                this.processing = true;
                Service.net().delete(this.route('discount.delete'), {
                    ids: this.selectedIds,
                }).then(data => {
                    this.processing = false;
                    this.closeModal('DeleteDiscount');
                    window.location.reload();
                });
            },
            changeStatus() {
                this.openModal('UpdateStatusDiscount');
            },
            approveChangeStatus() {
                if (!this.newStatus) {
                    return;
                }

                this.processing = true;
                Service.net().put(this.route('discount.status'), {
                    ids: this.selectedIds,
                    status: this.newStatus,
                }).then(data => {
                    this.processing = false;
                    this.closeModal('UpdateStatusDiscount');
                    window.location.reload();
                });
            },
            userName(id) {
                let user = this.userNames[id];
                return user ? user : 'N/A';
            },
            initiatorName(id) {
                if (!id) {
                    return 'Маркетплейс';
                }

                let merchant = this.merchantNames[id];
                return merchant ? merchant : 'N/A';
            },
            changePage(newPage) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: newPage,
                    filter: this.appliedFilter,
                }));
            },
            loadPage() {
                this.processing = true;
                let route = this.ifBundle() ?
                    this.route('bundle.pagination') :
                    this.route('discount.pagination');
                Service.showLoader();
                Service.net().get(route, {
                    page: this.currentPage,
                    filter: this.appliedFilter,
                }).then(data => {
                    this.discounts = data.iDiscounts;
                    this.total = data.total;
                    this.processing = false;
                    Service.hideLoader();
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
                    if (value && serverKeys.indexOf(key) !== -1) {
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
                this.applyFilter();
            },
            discountTypeName(type) {
                return (type in this.optionDiscountTypes) ? this.optionDiscountTypes[type].name : 'N/A';
            },
            forEachDiscount(callback) {
                for (let i in this.discounts) {
                    callback(this.discounts[i]['id']);
                }
            },
            changeSelectAll() {
                let newValue = !this.selectAll;
                let checkboxes = {};
                this.forEachDiscount((discountId) => {
                    checkboxes[discountId] = newValue;
                });
                this.checkboxes = checkboxes;
            },
            ifBundle() {
                return JSON.stringify(Object.keys(this.optionDiscountTypes).map(type => parseInt(type))) ===
                    JSON.stringify([this.discountTypes.bundleOffer, this.discountTypes.bundleMasterclass]);
            },
        },
        computed: {
            selectedIds() {
                return this.iDiscounts.filter(discount => {
                    return (discount.id in this.checkboxes) && this.checkboxes[discount.id];
                }).map(discount => discount.id);
            },
            selectedDiscounts() {
                return this.iDiscounts.filter((discount) => {
                    return (discount.id in this.checkboxes) && this.checkboxes[discount.id];
                });
            },
            initiatorsOptions() {
                return this.initiators.map(initiatorId => ({
                    value: initiatorId ? initiatorId : -1, text: this.initiatorName(initiatorId)
                }));
            },
            authorsOptions() {
                return this.authors.map(authorId => ({
                    value: authorId, text: this.userName(authorId)
                }));
            },
            discountTypesOptions() {
                return Object.values(this.optionDiscountTypes).map(type => ({value: type.id, text: type.name}));
            },
            discountStatusesOptions() {
                return Object.values(this.discountStatuses).map(type => ({value: type.id, text: type.name}));
            },
            fixDateTooltip() {
                return 'Искать точное совпадание с датой начала и/или окончания скидки';
            },
            countSelected() {
                return Object.values(this.checkboxes).reduce((acc, val) => { return acc + val; }, 0);
            },
            discountId() {
                let discountId = 0;
                if (this.countSelected !== 1) {
                    return discountId;
                }

                this.forEachDiscount((id) => {
                    if (id in this.checkboxes && this.checkboxes[id]) {
                        discountId = id;
                        return true;
                    }
                });
                return discountId;
            }
        },
        mounted() {
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
        },
        watch: {
            checkboxes: {
                deep: true,
                handler() {
                    for (let i in this.discounts) {
                        let discountId = this.discounts[i]['id'];
                        if (!(discountId in this.checkboxes && this.checkboxes[discountId])) {
                            this.selectAll = false;
                            return true;
                        }
                    }

                    this.selectAll = true;
                    return true;
                },
            },
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
