<template>
    <layout-main>
        <form id="form" novalidate v-on:submit.prevent.stop="add">
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
                        @update="updateCategories"
                    ></CategoriesSearch>

                    <BrandsSearch
                        key="brands-search-except"
                        classes="col-9 offset-3"
                        title="За исключением брендов"
                        :brands="brands"
                        @update="updateBrands"
                    ></BrandsSearch>

                    <div class="offset-3 col-9">
                        <v-input v-model="discount.offers" :help="'ID офферов через запятую'">За исключением офферов</v-input>
                    </div>
                </template>
            </div>

            <div class="row">
                <v-select v-model="discount.sizeType" :options="discountSizeTypes" class="col-3">Тип значения</v-select>
                <v-input v-model="discount.size" class="col-3" type="number" min="1"
                         :help="isPercentType(discount.sizeType) ? 'Значение от 1 до 100' : ''">
                    Значение в {{ isPercentType(discount.sizeType) ? 'процентах' : 'рублях' }}
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
                    <button type="submit" class="btn btn-success" :disabled="!valid">Создать скидку</button>
                </div>
            </div>
        </form>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('AddDiscount')">
                <div slot="header">
                    <b>Добавление скидки</b>
                </div>
                <div slot="body">
                    {{ this.result }}
                </div>
            </modal>
        </transition>
    </layout-main>
</template>

<script>
    import moment from 'moment';
    import Services from '../../../../../scripts/services/services';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import BrandsSearch from './components/brands-search.vue';
    import CategoriesSearch from './components/categories-search.vue';
    import Conditions from './components/conditions.vue';
    import {validationMixin} from 'vuelidate';
    import {required, requiredIf, decimal} from 'vuelidate/lib/validators';
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from "../../../../mixins/modal";

    moment.locale('ru');

    export default {
        name: 'page-discount-create',
        components: {
            modal,
            VInput,
            VSelect,
            BrandsSearch,
            Conditions,
            CategoriesSearch
        },
        mixins: [validationMixin, modalMixin],
        props: {
            discounts: Array,
            discountTypes: Object,
            iConditionTypes: Object,
            paymentMethods: Array,
            deliveryMethods: Array,
            categories: Array,
            brands: Array,
            roles: Array,
            iDistricts: Array,
        },
        data() {
            return {
                discount: {
                    name: null,
                    type: null,
                    size: null,
                    sizeType: null,
                    start_date: null,
                    end_date: null,
                    offers: null,
                    bundles: null,
                    brands: [],
                    categories: [],
                    conditions: [],
                },
                processing: false,
                result: '',
                // Тип скидки
                TYPE_OFFER: 1,
                TYPE_BUNDLE: 2,
                TYPE_BRAND: 3,
                TYPE_CATEGORY: 4,
                TYPE_DELIVERY: 5,
                TYPE_CART_TOTAL: 6,
            };
        },
        validations: {
            discount: {
                name: {required},
                type: {required},
                size: {decimal, required},
                sizeType: {required},
            },
            category: {
                required: requiredIf (function() { return this.discount.type === 2 })
            },
        },
        methods: {
            add() {
                if (!this.valid) {
                    return false;
                }

                let data = {
                    name: this.discount.name,
                    type: this.discount.type,
                    value: parseFloat(this.discount.size),
                    value_type: parseInt(this.discount.sizeType),
                    start_date: this.discount.start_date,
                    end_date: this.discount.end_date,
                    conditions: this.discount.conditions,
                    promo_code_only: !!this.discount.promo_code_only,
                };

                switch (this.discount.type) {
                    case this.TYPE_OFFER:
                        data.offers = this.formatIds(this.discount.offers);
                        break;
                    case this.TYPE_BUNDLE:
                        data.bundles = this.formatIds(this.discount.bundles);
                        break;
                    case this.TYPE_BRAND:
                        data.brands = this.discount.brands;
                        data.except = {};
                        data.except.offers = this.discount.offers ? this.formatIds(this.discount.offers) : [];
                        break;
                    case this.TYPE_CATEGORY:
                        data.categories = this.discount.categories;
                        data.except = {};
                        data.except.brands = this.discount.brands ? this.discount.brands : [];
                        data.except.offers = this.discount.offers ? this.formatIds(this.discount.offers) : [];
                        break;
                }

                this.processing = true;
                let err = 'Произошла ошибка при добавлении скидки.';
                let success = 'Скидка успешно добавлена.';
                Services.net().post(
                    this.route('discount.save'),
                    {},
                    data
                ).then(data => {
                    this.result = (data.status === 'ok') ? success : err;
                    this.openModal('AddDiscount');
                    this.processing = false;
                }, () => {
                    this.result = err;
                    this.openModal('AddDiscount');
                    this.processing = false;
                });
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
            formatIds(ids) {
                if (!ids) {
                    return [];
                }

                return ids
                    .split(',')
                    .map(id => { return parseInt(id); })
                    .filter(id => { return id > 0 });
            }
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
            topCategories() {
                return this.categories.filter(category => {
                    return !category.parent_id;
                });
            },
            selectedBrands() {
                let selected = new Set(this.brand);
                return this.brands.filter(brand => selected.has(brand.id));
            },
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
                let size = this.discount.size;
                let isPercent = this.discount.sizeType === 1;
                return size > 0 && (!isPercent || size <= 100);
            },
            checkDate() {
                let start = this.discount.start_date;
                let end = this.discount.end_date;
                return !(start && end) || (moment(start).unix() <= moment(end).unix());
            },
            valid() {
                let required = this.discount.name
                    && this.discount.type
                    && this.discount.size
                    && this.discount.sizeType
                    && !this.processing;

                return required
                    && this.checkType
                    && this.checkSize
                    && this.checkDate;
            }
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
        created() {
            Services.event().$on('discount-condition-add', val => this.discount.conditions.push(val));
            Services.event().$on('discount-condition-delete', condType => {
                this.discount.conditions = this.discount.conditions.filter(condition => { return condition.type !== condType; });
            });
        },
    };
</script>
