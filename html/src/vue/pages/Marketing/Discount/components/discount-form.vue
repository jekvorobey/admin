<template>
    <form id="form" novalidate v-on:submit.prevent.stop="send">
        <v-input v-model="discount.name"
                 :error="discountErrors.name"
                 @change="initErrorName"
        >Название</v-input>
        <div class="row">
            <v-select v-model="discount.type"
                      :options="optionDiscountTypes"
                      class="col-3"
                      :error="discountErrors.type"
                      @change="onTypeChange()"
            >Скидка на</v-select>

            <div v-if="discount.type === discountTypes.offer" class="col-9">
                <v-input v-model="discount.offers"
                         :help="'ID офферов через запятую'"
                         :error="discountErrors.offers"
                         @change="initErrorOffers"
                >Офферы</v-input>
            </div>

            <div v-if="discount.type === discountTypes.bundleOffer || discount.type === discountTypes.bundleMasterclass" class="col-9">
                <v-input v-model="discount.bundles"
                         :help="'ID бандлов через запятую'"
                         :error="discountErrors.bundles"
                         @change="initErrorBundles"
                >Бандлы</v-input>
            </div>

            <template v-if="discount.type === discountTypes.brand">
                <BrandsSearch
                        key="brands-search-main"
                        classes="col-9"
                        title="Бренды"
                        :brands="brands"
                        :i-brands="discount.brands"
                        :error="discountErrors.brands"
                        @update="updateBrands"
                ></BrandsSearch>

                <div class="offset-3 col-9">
                    <v-input v-model="discount.offers" :help="'ID офферов через запятую'">За исключением офферов</v-input>
                </div>
            </template>

            <template v-if="discount.type === discountTypes.category">
                <CategoriesSearch
                        classes="col-9"
                        title="Категории"
                        :categories="categories"
                        :i-categories="discount.categories"
                        :error="discountErrors.categories"
                        @update="updateCategories"
                ></CategoriesSearch>

                <BrandsSearch
                        key="brands-search-except"
                        classes="col-9 offset-3"
                        title="За исключением брендов"
                        :brands="brands"
                        :i-brands="discount.brands"
                        @update="updateBrands"
                ></BrandsSearch>

                <div class="offset-3 col-9">
                    <v-input v-model="discount.offers" :help="'ID офферов через запятую'">За исключением офферов</v-input>
                </div>
            </template>
        </div>

        <div class="row">
            <v-select v-model="discount.value_type"
                      :options="discountSizeTypes"
                      class="col-3"
                      :error="discountErrors.value_type"
                      @change="initErrorValueType"
            >Тип значения</v-select>
            <v-input v-model="discount.value"
                     class="col-3"
                     type="number"
                     min="1"
                     :help="isPercentType(discount.value_type) ? 'Значение от 1 до 100' : ''"
                     :error="discountErrors.value"
                     @change="initErrorValue"
            >Значение в {{ isPercentType(discount.value_type) ? 'процентах' : 'рублях' }}
            </v-input>
        </div>

        <div class="row">
            <div class="col-3 mb-3">
                <label for="start_date">Дата старта</label>
                <b-form-input id="start_date" v-model="discount.start_date" type="date"></b-form-input>
            </div>
            <div class="col-3">
                <label for="end_date">Дата окончания</label>
                <b-form-input id="end_date"
                              v-model="discount.end_date"
                              type="date"
                              :state="checkEndDate"
                              @change="initErrorEndDate"
                ></b-form-input>
                <b-form-invalid-feedback id="end_date-feedback">
                    {{ discountErrors.end_date }}
                </b-form-invalid-feedback>
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
                :discount="discount"
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
                :discountSizeTypes="discountSizeTypes"
        ></Conditions>

        <div class="row">
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-success">{{ submitText }}</button>
            </div>
        </div>
    </form>
</template>


<script>
    import moment from 'moment';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import BrandsSearch from '../../components/brands-search.vue';
    import Conditions from './conditions.vue';
    import CategoriesSearch from '../../components/categories-search.vue';
    import Services from '../../../../../scripts/services/services';

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
            optionDiscountTypes: Object,
            discountStatuses: Object,
            iConditionTypes: Object,
            paymentMethods: Array,
            deliveryMethods: Array,
            categories: Array,
            brands: Array,
            roles: Array,
            iDistricts: Array,
            submitText: String,
            processing: Boolean,
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

                discountErrors: {
                    name: null,
                    type: null,
                    offers: null,
                    bundles: null,
                    brands: null,
                    categories: null,
                    value_type: null,
                    value: null,
                    end_date: null,
                    conditions: null,
                },

                // Тип условия скидки
                CONDITION_TYPE_USER: 9,
            }
        },
        methods: {
            send() {
                if (!this.processing) {
                    let bool = true;
                    if (!(this.discount.name)) {
                        this.discountErrors.name = 'Обязательное поле!';
                        bool = false;
                    }
                    if (!(this.discount.type)) {
                        this.discountErrors.type = 'Обязательное поле!';
                        bool = false;
                    }
                    switch (this.discount.type) {
                        case this.discountTypes.offer:
                            if (!(this.discount.offers)) {
                                this.discountErrors.offers = "Введите значения ID офферов!";
                                bool = false;
                            } else if (!(!!this.discount.offers && this.formatIds(this.discount.offers).length > 0)) {
                                this.discountErrors.offers = "Введите значения ID офферов через запятую!";
                                bool = false;
                            }
                            break;
                        case this.discountTypes.bundleOffer:
                        case this.discountTypes.bundleMasterclass:
                            if (!(this.discount.bundles)) {
                                this.discountErrors.bundles = "Введите значения ID бандлов!";
                                bool = false;
                            } else if (!(!!this.discount.bundles && this.formatIds(this.discount.bundles).length > 0)) {
                                this.discountErrors.bundles = "Введите значения ID бандлов через запятую!";
                                bool = false;
                            }
                            break;
                        case this.discountTypes.brand:
                            if (!(this.discount.brands.length > 0)) {
                                this.discountErrors.brands = "Выберите хотя бы один бренд!";
                                bool = false;
                            }
                            break;
                        case this.discountTypes.category:
                            if (!(this.discount.categories.length > 0)) {
                                this.discountErrors.categories = "Выберите хотя бы одну категорию!";
                                bool = false;
                            }
                            break;
                        case this.discountTypes.delivery:
                        case this.discountTypes.cartTotal:
                            break;
                    }
                    if (!(this.discount.value_type)) {
                        this.discountErrors.value_type = 'Обязательное поле!';
                        bool = false;
                    }
                    if (!(this.discount.value)) {
                        this.discountErrors.value = "Обязательное поле!";
                        bool = false;
                    } else {
                        switch (this.discount.value_type) {
                            case 1:
                                if (!(this.discount.value > 0 && this.discount.value <= 100)) {
                                    this.discountErrors.value = "Значение должно быть в диапазоне от 1 до 100!";
                                    bool = false;
                                }
                                break;
                            case 2:
                                if (!(this.discount.value > 0)) {
                                    this.discountErrors.value = "Значение должно быть больше 0!";
                                    bool = false;
                                }
                                break;
                        }
                    }
                    let start = this.discount.start_date;
                    let end = this.discount.end_date;
                    if ((start && end) && !(moment(start).unix() <= moment(end).unix())) {
                        this.discountErrors.end_date = "Дата окончания не может быть меньше даты начала!";
                        bool = false;
                    }
                    if (bool) {
                        this.action(this.discount);
                    }
                }
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
                this.initErrorCategories();
            },
            updateBrands(brands) {
                this.discount = {...this.discount, brands};
                this.initErrorBrands();
            },
            onTypeChange() {
                this.discount.offers = null;
                this.discount.brands = [];
                this.discount.categories = [];
                this.initErrorType();
            },
            initDiscount() {
                if (!this.iDiscount) {
                    return;
                }

                let discount = {...this.iDiscount};
                discount.offers = Object.values(discount.offers).map(offer => offer.offer_id).join(',');
                discount.brands = Object.values(discount.brands).map(brand => brand.brand_id);
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
                        maxValueType: ('maxValueType' in cond) ? cond.maxValueType : null,
                        maxValue: ('maxValue' in cond) ? cond.maxValue : null,
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
            },
            initErrorName() {
                this.discountErrors.name = null;
            },
            initErrorType() {
                this.discountErrors.type = null;
                this.initErrorOffers();
                this.initErrorBrands();
                this.initErrorOffers();
            },
            initErrorOffers() {
                this.discountErrors.offers = null;
            },
            initErrorBundles() {
                this.discountErrors.bundles = null;
            },
            initErrorBrands() {
                this.discountErrors.brands = null;
            },
            initErrorCategories() {
                this.discountErrors.categories = null;
            },
            initErrorValueType() {
                this.discountErrors.value_type = null;
                this.initErrorValue();
            },
            initErrorValue() {
                this.discountErrors.value = null;
            },
            initErrorEndDate() {
                this.discountErrors.end_date = null;
            },
        },
        computed: {
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
            checkEndDate() {
                return (this.discountErrors.end_date) ? false : null;
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
