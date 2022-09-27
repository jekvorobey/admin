<template>
    <div>
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

                                <v-select v-model="filter.role_id" :options="filterData.roles" class="col-6">Роль</v-select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6">
                                <f-multi-select v-model="filter.merchant_id" :options="initiatorsOptions">
                                    Инициатор
                                </f-multi-select>
                            </div>
                            <div class="col-6">
                                <f-multi-select v-model="filter.user_id" :options="authorsOptions">
                                    Автор
                                </f-multi-select>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <label><b>Дата создания</b></label>
                            </div>
                            <f-date v-model="filter.created_at_from" class="col-3">От</f-date>
                            <f-date v-model="filter.created_at_to" class="col-3">До</f-date>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <label><b>Период действия скидки</b></label>
                            </div>
                            <f-date v-model="filter.start_date" class="col-3">От</f-date>
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
                            <f-date v-model="filter.end_date" class="col-3">До</f-date>
                            <div class="col-3">
                                <label>&nbsp;</label>
                                <div>
                                    <input class="ml-5" type="checkbox" v-model="filter.fix_end_date">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-3">
                                <label>Бессрочная</label>
                                <input class="ml-3 mt-3"
                                       type="checkbox"
                                       v-model="filter.indefinitely"
                                >
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

        <div class="row mb-3" v-if="canUpdate(blocks.marketing)">
            <div class="col-12 mt-3">
                <a :href="getRoute('discount.create')" class="btn btn-success">Создать скидку</a>

                <button class="btn btn-secondary" disabled v-if="discountId <= 0">Редактировать скидку</button>
                <a :href="getRoute('discount.edit', {id: discountId})" class="btn btn-secondary" v-else>Редактировать скидку</a>

                <button class="btn btn-danger" :disabled="countSelected < 1" @click="deleteDiscount()">Удалить
                    <template v-if="countSelected <= 1">скидку</template>
                    <template v-else>скидки</template>
                </button>
                <button class="btn btn-secondary" :disabled="countSelected < 1" @click="changeStatus()">Изменить статус
                    <template v-if="countSelected <= 1">скидки</template>
                    <template v-else>скидок</template>
                </button>
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
            </tr>
            </thead>
            <tbody>
            <tr v-if="discounts && Object.keys(discounts).length < 1">
                <td colspan="8" class="text-center">Скидки не найдены!</td>
            </tr>
            <tr v-if="discounts" v-for="(discount, index) in discounts">
                <td><input type="checkbox" v-model="checkboxes[discount.id]"></td>
                <td>{{ discount.id }}</td>
                <td>{{ datePrint(discount.created_at) }}</td>
                <td>{{ discount.name }}</td>
                <td>{{ discount.value }}{{ discount.value_type === DISCOUNT_VALUE_TYPE_RUB ? '₽' : '%' }} на<br/>
                    <b>{{ discountTypeName(discount.type) }}</b>
                </td>
                <td>{{ discount.validityPeriod }}</td>
                <td>{{ initiatorName(discount.merchant_id) }}</td>
                <td>{{ userName(discount.user_id) }}</td>
                <td :class="statusClass(discount)">
                    <span class="badge">{{ discount.statusName }}</span>
                </td>
            </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="total > pager.perPage"
                v-model="currentPage"
                :total-rows="total"
                :per-page="pager.perPage"
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
    </div>
</template>

<script>
    import FInput from '../../../../../components/filter/f-input.vue';
    import FMultiSelect from '../../../../../components/filter/f-multi-select.vue';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
    import VSelect2 from '../../../../../components/controls/VSelect2/v-select2.vue';
    import FDate from '../../../../../components/filter/f-date.vue';
    import DiscountList from '../../../../Marketing/Discount/components/discount-list.vue';

    import Services from '../../../../../../scripts/services/services.js';

    import modal from '../../../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../../../mixins/modal';

    const cleanHiddenFilter = {
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
    };
    const cleanFilter = Object.assign({
        id: '',
        name: '',
        type: [],
        status: [],
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
        name: 'slide-discounts',
        components: {
            FInput,
            FMultiSelect,
            VSelect,
            VSelect2,
            FDate,
            modal,
            DiscountList,
        },
        mixins: [modalMixin],
        props: ['id', 'legalName'],
        data() {
            let filter = Object.assign({}, cleanFilter);
            filter.status = filter.status.map(value => parseInt(value));

            return {
                opened: false,
                filter,
                filterData: {
                    discountStatuses: {},
                    discountTypes: {},
                    roles: [],
                    initiators: [],
                    authors: [],
                    userNames: {},
                },
                appliedFilter: {},
                currentPage: 1,
                processing: false,
                discounts: [],
                total: 0,
                pager: {},
                checkboxes: {},
                selectAll: false,
                newStatus: 0,

                // Тип значения
                DISCOUNT_VALUE_TYPE_PERCENT: 1,
                DISCOUNT_VALUE_TYPE_RUB: 2,

                // Статус скидки
                STATUS_CREATED: 1,
                STATUS_SENT: 2,
                STATUS_ON_CHECKING: 3,
                STATUS_ACTIVE: 4,
                STATUS_REJECTED: 5,
                STATUS_PAUSED: 6,
                STATUS_EXPIRED: 7,
            }
        },
        created() {
            Services.showLoader();
            Promise.all([
                Services.net().get(this.getRoute('merchant.detail.marketing.discounts.data', {id: this.id})),
                this.paginationPromise()
            ]).then(data => {
                this.filterData.discountStatuses = data[0].discountStatuses;
                this.filterData.discountTypes = data[0].discountTypes;
                this.filterData.roles = data[0].roles;
                this.filterData.initiators = data[0].initiators;
                this.filterData.authors = data[0].authors;
                this.filterData.userNames = data[0].userNames;
                this.pager = data[0].pager;

                this.discounts = data[1].discounts;
                this.total = data[1].total;
            }).finally(() => {
                Services.hideLoader();
            });
        },
        methods: {
            toggleHiddenFilter() {
                this.opened = !this.opened;
            },
            userName(id) {
                let user = this.filterData.userNames[id];
                return user ? user : 'N/A';
            },
            paginationPromise() {
                return Services.net().get(
                    this.getRoute(
                        'merchant.detail.marketing.discounts.pagination',
                        {id: this.id}
                    ),
                    {
                        page: this.currentPage,
                        filter: this.appliedFilter,
                    });
            },
            loadPage() {
                this.processing = true;
                Services.showLoader();
                this.paginationPromise().then(data => {
                    this.discounts = data.discounts;
                    this.total = data.total;
                    this.processing = false;
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            applyFilter() {
                this.currentPage = 1;
                let tmpFilter = {};
                for (let [key, value] of Object.entries(this.filter)) {
                    if (value && serverKeys.indexOf(key) !== -1) {
                        tmpFilter[key] = value;
                    }
                }
                this.appliedFilter = tmpFilter;
                this.loadPage();
            },
            clearFilter() {
                for (let entry of Object.entries(cleanFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.applyFilter();
            },
            discountTypeName(type) {
                return (type in this.filterData.discountTypes) ? this.filterData.discountTypes[type].name : 'N/A';
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
            deleteDiscount() {
                this.openModal('DeleteDiscount');
            },
            changeStatus() {
                this.openModal('UpdateStatusDiscount');
            },
            approveChangeStatus() {
                if (!this.newStatus) {
                    return;
                }

                this.processing = true;
                Services.showLoader();
                Service.net().put(this.route('discount.status'), {
                    ids: this.selectedIds,
                    status: this.newStatus,
                }).then(data => {
                    this.processing = false;
                    this.closeModal('UpdateStatusDiscount');
                    this.loadPage();
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            approveDelete() {
                this.processing = true;
                Services.showLoader();
                Services.net().delete(this.route('discount.delete'), {
                    ids: this.selectedIds,
                }).then(data => {
                    this.processing = false;
                    this.closeModal('DeleteDiscount');
                    this.loadPage();
                }).finally(() => {
                    Services.hideLoader();
                });
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
            initiatorName(id) {
                if (!id) {
                    return 'Маркетплейс';
                } else {
                    return this.legalName;
                }
            },
        },
        computed: {
            discountStatusesOptions() {
                return Object.values(this.filterData.discountStatuses).map(type => ({value: type.id, text: type.name}));
            },
            discountTypesOptions() {
                return Object.values(this.filterData.discountTypes).map(type => ({value: type.id, text: type.name}));
            },
            initiatorsOptions() {
                return this.filterData.initiators.map(initiatorId => ({
                    value: initiatorId ? initiatorId : -1, text: this.initiatorName(initiatorId)
                }));
            },
            authorsOptions() {
                return this.filterData.authors.map(authorId => ({
                    value: authorId, text: this.userName(authorId)
                }));
            },
            fixDateTooltip() {
                return 'Искать точное совпадание с датой начала и/или окончания скидки';
            },
            selectedDiscounts() {
                return this.discounts.filter((discount) => {
                    return (discount.id in this.checkboxes) && this.checkboxes[discount.id];
                });
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
            },
            selectedIds() {
                return this.discounts.filter(discount => {
                    return (discount.id in this.checkboxes) && this.checkboxes[discount.id];
                }).map(discount => discount.id);
            },
        },
        watch: {
            currentPage() {
                this.loadPage();
            }
        },
    };
</script>
