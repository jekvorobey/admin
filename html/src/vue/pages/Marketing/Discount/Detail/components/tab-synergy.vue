<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.id" class="col-2" type="number">
                        ID
                    </f-input>
                    <f-input v-model="filter.name" class="col-4">
                        Название
                    </f-input>
                    <f-select v-model="filter.status"
                                    :options="discountStatusesOptions"
                                    class="col-2">
                        Статус
                    </f-select>
                    <f-select v-model="filter.type"
                                    :options="discountTypesOptions"
                                    :name="'type'"
                                    class="col-2">
                        Скидка на
                    </f-select>
                    <div class="col-4">
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
                                       :checked="filter.indefinitely">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-sm btn-dark" @click="load">Применить</button>
                <button class="btn btn-sm btn-outline-dark" @click="clearFilter">Очистить</button>
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>ID</th>
                <th>Дата создания</th>
                <th>Название</th>
                <th>Скидка</th>
                <th>Период действия</th>
                <th>Статус</th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="!discountSynergy">
                <td colspan="9" class="text-center">Скидки не найдены!</td>
            </tr>
            <tr v-if="discountSynergy" v-for="discount in discountSynergy">
                <td>{{ discount.id }}</td>
                <td>{{ datePrint(discount.created_at) }}</td>
                <td><a :href="link(discount.id)">{{ discount.name }}</a></td>
                <td>{{ discount.value }}{{ discount.value_type === DISCOUNT_VALUE_TYPE_RUB ? '₽' : '%' }} на<br/>
                    <b>{{ discountTypeName(discount.type) }}</b>
                </td>
                <td>{{ discount.validityPeriod }}</td>
                <td :class="statusClass(discount)">
                    <span class="badge">{{ discount.statusName }}</span>
                </td>
            </tr>
            </tbody>
        </table>

        <b-pagination
            v-if="pager.count > pager.perPage"
            v-model="pager.page"
            :total-rows="pager.count"
            :per-page="pager.perPage"
            class="mt-3 float-right"
        />
    </div>

</template>

<script>
import FInput from "../../../../../components/filter/f-input.vue";
import FDate from '../../../../../components/filter/f-date.vue';
import FSelect from '../../../../../components/filter/f-select.vue';
import FMultiSelect from '../../../../../components/filter/f-multi-select.vue';
import Service from "../../../../../../scripts/services/services";

export default {
    name: 'tab-synergy',
    components: {
        FInput,
        FDate,
        FSelect,
        FMultiSelect,
    },
    props: {
        model: Object,
        iDiscounts: [Array, null],
        optionDiscountTypes: {},
        discountStatuses: {},
    },
    data() {
        return {
            discounts: this.iDiscounts,
            pager: {
                page: 1,
                count: 1,
                perPage: 15,
            },
            filter: {
                id: null,
                name: null,
                status: null,
                type: null,
                start_date: null,
                fix_start_date: null,
                end_date: null,
                fix_end_date: null,
                indefinitely: null,
            },

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
        }
    },
    computed: {
        discountSynergy() {
            return this.discounts.filter((discount) => {
                return this.model.conditions.synergy ? this.model.conditions.synergy.indexOf(discount.id) === -1 : false;
            });
        },
        discountTypesOptions() {
            return Object.values(this.optionDiscountTypes).map(type => ({value: type.value, text: type.text}));
        },
        discountStatusesOptions() {
            return Object.values(this.discountStatuses).map(type => ({value: type.value, text: type.text}));
        },
        fixDateTooltip() {
            return 'Искать точное совпадание с датой начала и/или окончания скидки';
        },
    },
    methods: {
        link(discountId) {
            return this.getRoute('discount.detail', {id: parseInt(discountId)});
        },
        load() {
            let filter = {};
            for (let k in this.filter) {
                if (this.filter[k]) {
                    filter[k] = this.filter[k];
                }
            }

            this.processing = true;
            let route = this.ifBundle() ?
                this.route('bundle.pagination') :
                this.route('discount.pagination');
            Service.showLoader();
            Service.net().get(route, {
                page: this.pager.page,
                perPage: this.pager.perPage,
                filter: filter,
            }).then(data => {
                this.discounts = data.iDiscounts;
                this.pager.count = data.total;
                this.processing = false;
                Service.hideLoader();
            });
        },
        clearFilter() {
            this.filter = {
                id: null,
                name: null,
                status: null,
                type: null,
                start_date: null,
                fix_start_date: null,
                end_date: null,
                fix_end_date: null,
                indefinitely: null,
            }
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
        ifBundle() {
            return JSON.stringify(Object.keys(this.optionDiscountTypes).map(type => parseInt(type))) ===
                JSON.stringify([this.discountTypes.bundleOffer, this.discountTypes.bundleMasterclass]);
        },
        discountTypeName(type) {
            return (type in this.optionDiscountTypes) ? this.optionDiscountTypes[type].name : 'N/A';
        },
    },
    mounted() {

    },
    created() {
        this.load();
    },
    watch: {
        'pager.page': 'load',
    },
};
</script>

<style scoped>
.condition-list {
    line-height: 0.9;
    margin-top: 10px;
}
</style>
