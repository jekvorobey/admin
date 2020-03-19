<template>
    <form id="form" novalidate v-on:submit.prevent.stop="send">
        <v-input v-model="discount.name">Название</v-input>
        <div class="row">
            <v-select v-model="discount.type" :options="discountTypes" class="col-3" @change="onTypeChange()">Скидка на</v-select>

            <div v-if="discount.type === TYPE_OFFER" class="col-9">
                <v-input v-model="discount.offers" :help="'ID офферов через запятую'">Офферы</v-input>
            </div>

            <div v-if="discount.type === TYPE_BUNDLE" class="col-9">
                <v-input v-model="discount.bundles" :help="'ID бандлов через запятую'">Бандлы</v-input>
            </div>

            <template v-if="discount.type === TYPE_BRAND">
                <BrandsSearch
                        key="brands-search-main"
                        classes="col-9"
                        title="Бренды"
                        :brands="brands"
                        :i-brands="discount.brands"
                        @update="updateBrands"
                ></BrandsSearch>

                <div class="offset-3 col-9">
                    <v-input v-model="discount.offers" :help="'ID офферов через запятую'">За исключением офферов</v-input>
                </div>
            </template>

            <template v-if="discount.type === TYPE_CATEGORY">
                <CategoriesSearch
                        classes="col-9"
                        title="Категории"
                        :categories="categories"
                        :i-categories="discount.categories"
                        @update="updateCategories"
                ></CategoriesSearch>

                <BrandsSearch
                        key="brands-search-except"
                        classes="col-9 offset-3"
                        title="За исключением брендов"
                        :brands="brands"
                        :i-brands="discount.exceptionBrands"
                        @update="updateBrands"
                ></BrandsSearch>

                <div class="offset-3 col-9">
                    <v-input v-model="discount.offers" :help="'ID офферов через запятую'">За исключением офферов</v-input>
                </div>
            </template>
        </div>

        <div class="row">
            <v-select v-model="discount.value_type" :options="discountSizeTypes" class="col-3">Тип значения</v-select>
            <v-input v-model="discount.value" class="col-3" type="number" min="1"
                     :help="isPercentType(discount.value_type) ? 'Значение от 1 до 100' : ''">
                Значение в {{ isPercentType(discount.value_type) ? 'процентах' : 'рублях' }}
            </v-input>
        </div>

        <div class="row">
            <div class="col-3 mb-3">
                <label for="start_date">Дата старта</label>
                <b-form-input id="start_date" v-model="discount.start_date" type="date"></b-form-input>
            </div>
            <div class="col-3">
                <label for="end_date">Дата окончания</label>
                <b-form-input id="end_date" v-model="discount.end_date" type="date"></b-form-input>
            </div>
        </div>

        <div class="row">
            <v-select v-model="discount.status" :options="discountStatuses" class="col-3">Статус</v-select>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="promo_code_only" v-model="discount.promo_code_only">
                    <label class="form-check-label" for="promo_code_only">
                        Скидка действительна только по промокоду
                    </label>
                </div>
            </div>
        </div>

        <Conditions
                :discounts="discounts"
                :conditions="discount.conditions"
                :iConditionTypes="iConditionTypes"
                :paymentMethods="paymentMethods"
                :deliveryMethods="deliveryMethods"
                :regions="discountRegions"
                :segments="segments"
                :roles="roles"
                :brands="brands"
                :categories="categories"
        ></Conditions>

        <div class="row">
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-success" :disabled="!valid">Сохранить скидку</button>
            </div>
        </div>
    </form>
</template>


<script>
    import moment from 'moment';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import BrandsSearch from './brands-search.vue';
    import Conditions from './conditions.vue';
    import CategoriesSearch from './categories-search.vue';
    import Services from "../../../../../scripts/services/services";

    moment.locale('ru');

    export default {
        components: {
            VInput,
            VSelect,
            BrandsSearch,
            Conditions,
            CategoriesSearch,
        },
        props: {
            iDiscount: Object,
            discounts: Array,
            discountTypes: Object,
            discountStatuses: Object,
            iConditionTypes: Object,
            paymentMethods: Array,
            deliveryMethods: Array,
            categories: Array,
            brands: Array,
            roles: Array,
            iDistricts: Array,
            action: Function,
        },
        data() {
            return {
                discount: {
                    name: null,
                    type: null,
                    value: null,
                    value_type: null,
                    start_date: null,
                    end_date: null,
                    offers: null,
                    bundles: null,
                    status: 1, // STATUS_ACTIVE
                    brands: [],
                    categories: [],
                    conditions: [],
                },

                // Тип скидки
                TYPE_OFFER: 1,
                TYPE_BUNDLE: 2,
                TYPE_BRAND: 3,
                TYPE_CATEGORY: 4,
                TYPE_DELIVERY: 5,
                TYPE_CART_TOTAL: 6,

                // Тип условия скидки
                CONDITION_TYPE_USER: 9,
            }
        },
        methods: {
            send() {
                this.action(this.discount);
            },
            formatIds(ids) {
                if (!ids) {
                    return [];
                }

                return ids
                    .split(',')
                    .map(id => { return parseInt(id); })
                    .filter(id => { return id > 0 });
            },
            isPercentType(type) {
                return type === 1;
            },
            updateCategories(categories) {
                this.discount = {...this.discount, categories};
            },
            updateBrands(brands) {
                this.discount = {...this.discount, brands};
            },
            onTypeChange() {
                this.discount.offers = null;
                this.discount.brands = [];
                this.discount.categories = [];
            },
            initDiscount() {
                if (!this.iDiscount) {
                    return;
                }

                let discount = {...this.iDiscount};
                discount.offers = Object.values(discount.offers).map(offer => offer.offer_id).join(',');
                discount.exceptionBrands = Object.values(discount.brands)
                    .filter(brand => brand.except)
                    .map(brand => brand.brand_id);
                discount.brands = Object.values(discount.brands)
                    .filter(brand => !brand.except)
                    .map(brand => brand.brand_id);
                discount.categories = Object.values(discount.categories).map(category => category.category_id);

                let roles = discount.roles.map(role => role.role_id);
                let segments = discount.segments.map(segment => segment.segment_id);
                let conditionToObject = item => {
                    let cond = item.condition ? item.condition : {};
                    return  {
                        categories: ('categories' in cond) ? cond.categories : [],
                        deliveryMethods: ('deliveryMethods' in cond) ? cond.deliveryMethods : [],
                        paymentMethods: ('paymentMethods' in cond) ? cond.paymentMethods : [],
                        regions: ('regions' in cond) ? cond.regions : [],
                        roles: (item.type === this.CONDITION_TYPE_USER) ? roles : [],
                        segments: (item.type === this.CONDITION_TYPE_USER) ? segments : [],
                        sum: ('minPrice' in cond) ? cond.minPrice : "",
                        synergy: ('synergy' in cond) ? cond.synergy : [],
                        type: item.type,
                        users: ('customerIds' in cond) ? cond.customerIds : [],
                        count: ('count' in cond) ? cond.count : '',
                        offer: ('offer' in cond) ? cond.offer : '',
                        brands: ('brands' in cond) ? cond.brands : '',
                        sequenceNumber: ('orderSequenceNumber' in cond) ? cond.orderSequenceNumber : '',
                    }
                };

                discount.conditions = Object.values(discount.conditions).map(item => conditionToObject(item));
                if (discount.conditions.filter(item => item.type === this.CONDITION_TYPE_USER).length === 0) {
                    if (roles.length > 0 || segments.length > 0) {
                        discount.conditions.push(conditionToObject({
                            'type': this.CONDITION_TYPE_USER,
                            'condition': {
                                'roles': roles,
                                'segments': segments,
                            }
                        }));
                    }
                }

                this.discount = discount;
            }
        },
        computed: {
            checkType() {
                switch (this.discount.type) {
                    case this.TYPE_OFFER:
                        return !!this.discount.offers && this.formatIds(this.discount.offers).length > 0;
                    case this.TYPE_BUNDLE:
                        return !!this.discount.bundles && this.formatIds(this.discount.bundles).length > 0;
                    case this.TYPE_BRAND:
                        return this.discount.brands.length > 0;
                    case this.TYPE_CATEGORY:
                        return this.discount.categories.length > 0;
                    case this.TYPE_DELIVERY:
                    case this.TYPE_CART_TOTAL:
                        return true;
                }
                return false;
            },
            checkSize() {
                let value = this.discount.value;
                let isPercent = this.discount.value_type === 1;
                return value > 0 && (!isPercent || value <= 100);
            },
            checkDate() {
                let start = this.discount.start_date;
                let end = this.discount.end_date;
                return !(start && end) || (moment(start).unix() <= moment(end).unix());
            },
            valid() {
                let required = this.discount.name
                    && this.discount.type
                    && this.discount.value
                    && this.discount.value_type
                    && !this.processing;

                return required
                    && this.checkType
                    && this.checkSize
                    && this.checkDate;
            },
            discountSizeTypes() {
                return [
                    {text: 'Проценты', value: 1},
                    {text: 'Рубли', value: 2}
                ];
            },
            segments() {
                return [
                    {text: 'A', value: 1},
                    {text: 'B', value: 2},
                    {text: 'C', value: 3}
                ];
            },
            discountRegions() {
                let regions = [];
                for (let i in this.iDistricts) {
                    let district = this.iDistricts[i];
                    district.regions.map(r => {
                        regions.push({text: r.name, value: r.id, group: district.name});
                    });
                }
                return regions;
            },
        },
        watch: {
            'discount.offers': {
                handler(val, oldVal) {
                    if (val && val !== oldVal) {
                        let format = this.formatIds(this.discount.offers).join(', ');
                        let separator = val.slice(-1) === ','
                            ? ','
                            : (val.slice(-2) === ', ' ? ', ' : '');
                        this.discount.offers = format + separator;
                    }
                },
            },
            'discount.bundles': {
                handler(val, oldVal) {
                    if (val && val !== oldVal) {
                        let format = this.formatIds(this.discount.bundles).join(', ');
                        let separator = val.slice(-1) === ','
                            ? ','
                            : (val.slice(-2) === ', ' ? ', ' : '');
                        this.discount.bundles = format + separator;
                    }
                },
            },
        },
        mounted() {
            this.initDiscount();
        },
        created() {
            Services.event().$on('discount-condition-add', val => this.discount.conditions.push(val));
            Services.event().$on('discount-condition-delete', condType => {
                this.discount.conditions = this.discount.conditions.filter(condition => { return condition.type !== condType; });
            });
        },
    }
</script>
