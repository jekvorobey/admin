<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="4">
                Инфопанель
                <button class="btn btn-success btn-sm" @click="save">
                    Сохранить
                </button>
                <button @click="cancel" class="btn btn-outline-danger btn-sm">Отмена</button>
            </th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <th>ID</th>
                <td colspan="2">{{ discount.id }}</td>
            </tr>
            <tr>
                <th>Дата создания</th>
                <td colspan="2">{{ discount.created_at }}</td>
            </tr>
            <tr>
                <th><label for="discount-name-input">Название</label></th>
                <td colspan="2">
                    <v-input id="discount-name-input"
                             v-model="$v.discount.name.$model"
                             :group="false"
                             :sm="true"
                             :error="errorDiscountName"/>
                </td>
            </tr>
            <tr>
                <th><label for="discount-value-input">Размер</label></th>
                <td>
                    <v-input id="discount-value-input"
                           v-model="$v.discount.value.$model"
                           :group="false"
                           :sm="true"
                           :error="errorDiscountValue"
                    />
                </td>
                <td>
                    <select class="form-control form-control-sm" v-model="discount.value_type">
                        <option v-for="sizeType in discountSizeTypes" :value="sizeType.value">
                            {{ sizeType.text }}
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="discount-type-select">Скидка на</label></th>
                <td colspan="2">
                    <select class="form-control form-control-sm" id="discount-type-select" v-model="discount.type" @change="onTypeChange">
                        <option v-for="type in optionDiscountTypes" :value="type.value">
                            {{ type.text }}
                        </option>
                    </select>
                </td>
            </tr>

            <tr v-show="showCategories">
                <th><label for="discount-type-select">{{ categoriesTitle }}</label></th>
                <td colspan="2">
                    <CategoriesSearch
                            :categories="categories"
                            :i-categories="discount.categories"
                            :error="errorCategories"
                            @update="updateCategories"
                    ></CategoriesSearch>
                </td>
            </tr>
            <tr v-show="showBrands">
                <th><label for="discount-type-select">{{ brandsTitle }}</label></th>
                <td colspan="2">
                    <BrandsSearch
                            key="brands-search-except"
                            :brands="brands"
                            :i-brands="discount.brands"
                            :error="errorBrands"
                            @update="updateBrands"
                    ></BrandsSearch>
                </td>
            </tr>
            <tr v-show="showOffers">
                <th><label for="discount-type-select">{{ offersTitle }}</label></th>
                <td colspan="2">
                    <v-input v-model="$v.discount.offers.$model"
                             :help="'ID офферов через запятую'"
                             :error="errorOffers"
                    ></v-input>
                </td>
            </tr>
            <tr v-show="showBundle">
                <th><label for="discount-type-select">{{ bundleTitle }}</label></th>
                <td colspan="2">
                    <v-input v-model="$v.discount.bundleItems.$model"
                             :help="'ID офферов через запятую'"
                             :error="errorBundles"
                    ></v-input>
                </td>
            </tr>
            <tr v-show="showMasterClasses">
                <th><label for="discount-type-select">Мастер-классы</label></th>
                <td colspan="2">
                    <v-input v-model="$v.discount.bundleItems.$model"
                             :help="'ID мастер-классов через запятую'"
                             :error="errorMasterClasses"
                    ></v-input>
                </td>
            </tr>

            <tr>
                <th>Период действия скидки</th>
                <td>
                    <input type="date" v-model="discount.start_date" class="form-control form-control-sm"/>
                </td>
                <td>
                    <input type="date" v-model="discount.end_date" class="form-control form-control-sm"/>
                </td>
            </tr>
            <tr>
                <th>
                    <span class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="discount-merchant-btn" key="merchantBtn" v-model="merchantBtn">
                        <label class="custom-control-label" for="discount-merchant-btn"></label>
                        <label for="discount-merchant-btn">Инициатор скидки</label>
                    </span>
                </th>
                <td colspan="2">
                    <template v-if="merchantBtn">
                        <select class="form-control form-control-sm"
                                   :class="{ 'is-invalid': errorDiscountMerchantId }"
                                   id="discount-merchant-select"
                                   v-model="$v.discount.merchant_id.$model">
                            <option v-for="merchant in merchants" :value="merchant.id">
                                {{ merchant.name }}
                            </option>
                        </select>
                        <span class="invalid-feedback" role="alert">
                            {{ errorDiscountMerchantId }}
                        </span>
                    </template>
                    <template v-else>
                        Маркетплейс
                    </template>
                </td>

            </tr>
            <tr>
                <th>Автор</th>
                <td colspan="2">
                    {{ author ? author.full_name : 'N/A' }}
                </td>
            </tr>
            <tr>
                <th><label for="discount-status-select">Статус</label></th>
                <td colspan="2">
                    <select class="form-control form-control-sm" id="discount-status-select" v-model="discount.status">
                        <option v-for="status in discountStatuses" :value="status.value">
                            {{ status.text }}
                        </option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    import Services from "../../../../../../scripts/services/services";
    import VSelect2 from '../../../../../components/controls/VSelect2/v-select2.vue';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import BrandsSearch from '../../../components/brands-search.vue';
    import CategoriesSearch from '../../../components/categories-search.vue';
    import {required,requiredIf,minValue,maxValue} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    export default {
        name: 'discount-detail-infopanel',
        components: {
            VInput,
            VSelect2,
            BrandsSearch,
            CategoriesSearch
        },
        mixins: [
            validationMixin,
        ],
        props: {
            model: Object,
            optionDiscountTypes: Object,
            discountStatuses: Object,
            merchants: Array,
            author: Object,
            categories: Array,
            brands: Array,
        },
        data() {
            return {
                merchantBtn: false,

                // Тип значения
                DISCOUNT_VALUE_TYPE_PERCENT: 1,
                DISCOUNT_VALUE_TYPE_RUB: 2,
            };
        },
        validations() {
            let self = this;

            return {
                discount: {
                    name: { required },
                    value: { required, minValue: minValue(1), maxValue: maxValue(self.discountMaxValue) },
                    merchant_id: { required: requiredIf(() => {
                        return self.merchantBtn;
                    }) },
                    categories: { required: requiredIf(() => {
                            return self.discount.type === self.discountTypes.category
                    }) },
                    brands: { required: requiredIf(() => {
                            return self.discount.type === self.discountTypes.brand
                        }) },
                    offers: { required: requiredIf(() => {
                            return self.discount.type === self.discountTypes.offer
                        }) },
                    bundleItems: { required: requiredIf(() => {
                        return [
                            self.discountTypes.bundleMasterclass,
                            self.discountTypes.bundleOffer,
                        ].includes(self.discount.type);
                    }) },
                }
            };
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                let discount = this.discount;
                let data = {
                    name: discount.name,
                    type: discount.type,
                    value: parseFloat(discount.value),
                    value_type: parseInt(discount.value_type),
                    start_date: discount.start_date,
                    end_date: discount.end_date,
                    conditions: discount.conditions,
                    status: discount.status,
                    promo_code_only: !!discount.promo_code_only,
                };

                switch (discount.type) {
                    case this.discountTypes.offer:
                        data.offers = this.formatIds(discount.offers);
                        break;
                    case this.discountTypes.bundleOffer:
                    case this.discountTypes.bundleMasterclass:
                        data.bundle_items = this.formatIds(discount.bundleItems);
                        break;
                    case this.discountTypes.brand:
                        data.brands = discount.brands;
                        data.except = {};
                        data.except.offers = discount.offers ? this.formatIds(discount.offers) : [];
                        break;
                    case this.discountTypes.category:
                        data.categories = discount.categories;
                        data.except = {};
                        data.except.brands = discount.brands ? discount.brands : [];
                        data.except.offers = discount.offers ? this.formatIds(discount.offers) : [];
                        break;
                }

                this.processing = true;
                let err = 'Произошла ошибка при сохранении скидки.';
                let success = 'Скидка успешно обновлена.';
                Services.showLoader();
                Services.net().put(
                    this.getRoute('discount.update', {id: discount.id}),
                    {},
                    data
                ).then(data => {
                    Services.msg(success);
                    location.reload();
                }, () => {
                    Services.msg(err, 'danger');
                }).finally(() => {
                    this.processing = false;
                    Services.hideLoader();
                });
            },
            onTypeChange() {
                this.discount.offers = null;
                this.discount.brands = [];
                this.discount.categories = [];
                this.discount.bundleItems = [];
                this.$v.discount.brands.$model = [];
                this.$v.discount.categories.$model = [];
            },
            updateBrands(brands) {
                this.discount = {...this.discount, brands};
                this.$v.discount.brands.$model = brands;
            },
            updateCategories(categories) {
                this.discount = {...this.discount, categories};
                this.$v.discount.categories.$model = categories;
            },
            cancel() {
                this.$emit('initDiscount');
            },
            formatIds(ids) {
                if (typeof(ids) !== 'string') {
                    return [];
                }

                return ids
                    .split(',')
                    .map(id => { return parseInt(id); })
                    .filter(id => { return id > 0 });
            },
        },
        computed: {
            discount: {
                get() {return this.model},
                set(value) {this.$emit('update:discount', value)},
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
            showCategories() {
                return [this.discountTypes.category].includes(this.discount.type);
            },
            showBrands() {
                return [
                    this.discountTypes.category,
                    this.discountTypes.brand
                ].includes(this.discount.type);
            },
            showOffers() {
                return [
                    this.discountTypes.category,
                    this.discountTypes.brand,
                    this.discountTypes.offer,
                ].includes(this.discount.type);
            },
            showMasterClasses() {
                return [
                    this.discountTypes.bundleMasterclass,
                ].includes(this.discount.type);
            },
            showBundle() {
                return [
                    this.discountTypes.bundleOffer,
                ].includes(this.discount.type);
            },
            categoriesTitle() {
                return 'Категории';
            },
            brandsTitle() {
                return this.discount.type === this.discountTypes.brand ? 'Бренды' : 'За исключением брендов';
            },
            offersTitle() {
                return this.discount.type === this.discountTypes.offer ? 'Офферы' : 'За исключением офферов';
            },
            bundleTitle() {
                return  'Бандл из товаров';
            },
            discountMaxValue() {
                return this.discount.value_type === this.DISCOUNT_VALUE_TYPE_PERCENT ? 100 : Infinity;
            },
            errorDiscountName() {
                if (this.$v.discount.name.$dirty) {
                    if (!this.$v.discount.name.required) {
                        return "Обязательное поле";
                    }
                }
            },
            errorDiscountValue() {
                if (this.$v.discount.value.$dirty) {
                    if (!this.$v.discount.value.required) return "Обязательное поле";
                    if (!this.$v.discount.value.minValue) return "Значение должно быть ≥ 1";
                    if (!this.$v.discount.value.maxValue) return "Значение должно быть ≤ " + this.discountMaxValue;
                }
            },
            errorDiscountMerchantId() {
                if (this.$v.discount.merchant_id.$invalid) {
                    if (!this.$v.discount.merchant_id.required) return "Обязательное поле";
                }
            },
            errorCategories() {
                if (this.$v.discount.categories.$dirty) {
                    if (!this.$v.discount.categories.required) return "Обязательное поле";
                }
            },
            errorBrands() {
                if (this.$v.discount.brands.$dirty) {
                    if (!this.$v.discount.brands.required) return "Обязательное поле";
                }
            },
            errorOffers() {
                if (this.$v.discount.offers.$dirty) {
                    if (!this.$v.discount.offers.required) return "Обязательное поле";
                }
            },
            errorMasterClasses() {
                if (this.$v.discount.bundleItems.$dirty) {
                    if (!this.$v.discount.bundleItems.required) return "Обязательное поле";
                }
            },
            errorBundles() {
                if (this.$v.discount.bundleItems.$dirty) {
                    if (!this.$v.discount.bundleItems.required) return "Обязательное поле";
                }
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
            'discount.bundleItems': {
                handler(val, oldVal) {
                    if (val && val !== oldVal) {
                        let format = this.formatIds(this.discount.bundleItems).join(', ');
                        let separator = val.slice(-1) === ','
                            ? ','
                            : (val.slice(-2) === ', ' ? ', ' : '');
                        this.discount.bundleItems = format + separator;
                    }
                },
            },
        },
        mounted() {
            this.merchantBtn = this.discount.merchant_id > 0;
        },
    };
</script>

