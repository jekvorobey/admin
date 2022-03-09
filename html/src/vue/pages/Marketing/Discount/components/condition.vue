<template>
    <div class="row">
        <div class="col-4">
            <v-select
                v-model="conditionType"
                :options="allowedTypes"
                :error="conditionError"
            >Условия предоставления скидки</v-select>
        </div>
        <div class="col-8">
            <div class="col-8">
                <!-- На заказ от определенной суммы -->
                <div v-if="conditionType === CONDITION_TYPE_MIN_PRICE_ORDER">
                    <v-input
                        v-model="values.sum"
                        type="number"
                        min="0"
                        :error="valuesErrors.sum"
                        @change="initSumError"
                    >От (руб.)</v-input>
                </div>

                <!-- На заказ от определенной суммы товаров заданного бренда -->
                <template v-if="conditionType === CONDITION_TYPE_MIN_PRICE_BRAND">
                    <div>
                        <v-input
                            v-model="values.sum"
                            type="number"
                            min="0"
                            :error="valuesErrors.sum"
                            @change="initSumError"
                        >От (руб.)</v-input>
                    </div>

                    <BrandsSearch
                        title="Бренды"
                        key="brands-search-new-condition"
                        :brands="brands"
                        :i-brands="values.brands"
                        :error="valuesErrors.brands"
                        @update="updateBrandList"
                    ></BrandsSearch>
                </template>

                <!-- На заказ от определенной суммы товаров заданной категории -->
                <template v-if="conditionType === CONDITION_TYPE_MIN_PRICE_CATEGORY">
                    <div>
                        <v-input
                            v-model="values.sum"
                            type="number"
                            min="0"
                            :error="valuesErrors.sum"
                            @change="initSumError"
                        >От (руб.)</v-input>
                    </div>

                    <CategoriesSearch
                        title="Категории"
                        key="categories-search-new-condition"
                        :categories="categories"
                        :i-categories="values.categories"
                        :error="valuesErrors.categories"
                        @update="updateCategoriesList"
                    ></CategoriesSearch>
                </template>

                <!-- На количество единиц одного товара -->
                <template v-if="conditionType === CONDITION_TYPE_EVERY_UNIT_PRODUCT">
                    <div>
                        <v-input
                            v-model="values.count"
                            type="number"
                            min="0"
                            :error="valuesErrors.count"
                            @change="initCountError"
                        >Количество</v-input>
                    </div>

                    <div>
                        <v-input
                            v-model="values.offer"
                            type="number"
                            min="0"
                            :error="valuesErrors.offer"
                            @change="initOfferError"
                        >Оффер</v-input>
                    </div>
                </template>

                <!-- На способ доставки -->
                <div v-if="conditionType === CONDITION_TYPE_DELIVERY_METHOD">
                    <v-select
                        v-model="values.deliveryMethods"
                        :options="iDeliveryMethods"
                        :multiple="true"
                        :error="valuesErrors.deliveryMethods"
                        @change="initDeliveryMethodsError"
                    >Способ доставки</v-select>
                </div>

                <!-- На способ оплаты -->
                <div v-if="conditionType === CONDITION_TYPE_PAY_METHOD">
                    <v-select
                        v-model="values.paymentMethods"
                        :options="iPaymentMethods"
                        :multiple="true"
                        :error="valuesErrors.paymentMethods"
                        @change="initPaymentMethodsError"
                    >Способ оплаты</v-select>
                </div>

                <!-- Территория действия (регион с точки зрения адреса доставки заказа) -->
                <div v-if="conditionType === CONDITION_TYPE_REGION">
                    <f-multi-select
                        v-model="values.regions"
                        :options="regions"
                        name="condition-type-region"
                        grouped
                        multiple
                        :error="valuesErrors.regions"
                        @input="initRegionsError"
                    >Регионы</f-multi-select>
                </div>

                <div v-if="valuesErrors.user" class="mb-2" :class="{ 'error': valuesErrors.user }">
                    {{ valuesErrors.user }}
                </div>

                <div v-if="conditionType === CONDITION_TYPE_USER">
                    <!-- Для определенных пользователей системы -->
                    <v-input
                        v-model="values.users"
                        :error="valuesUserError"
                        @change="initUserError"
                    >ID пользователей (через запятую)</v-input>

                    <!-- Для определенных пользовательских сегментов -->
                    <v-select
                        v-model="values.segments"
                        :options="segments"
                        :multiple="true"
                        :error="valuesUserError"
                        @change="initUserError"
                    >Сегменты</v-select>

                    <!-- Для определенных пользовательских ролей -->
                    <v-select
                        v-model="values.roles"
                        :options="roles"
                        :multiple="true"
                        :error="valuesUserError"
                        @change="initUserError"
                    >Роли</v-select>
                </div>

                <!-- Порядковый номер заказа -->
                <div v-if="conditionType === CONDITION_TYPE_ORDER_SEQUENCE_NUMBER">
                    <v-input
                        v-model="values.sequenceNumber"
                        type="number"
                        min="0"
                        :error="valuesErrors.sequenceNumber"
                        @change="initSequenceNumberError"
                    >Порядковый номер заказа</v-input>
                </div>

                <!-- Суммируется с другими маркетинговыми инструментами -->
                <div v-if="conditionType === CONDITION_TYPE_DISCOUNT_SYNERGY">
                    <div class="form-group">
                        <label for="synergy-select">
                            Суммируется с другими скидками
                        </label>

                        <v-select2
                            v-model="values.synergy"
                            id="synergy-select"
                            class="form-control"
                            multiple
                            @change="initSynergyError"
                        >
                            <option v-for="discount in discounts" :key="discount.value" :value="discount.value">
                                {{ discount.text }}
                            </option>
                        </v-select2>

                        <div v-if="valuesErrors.synergy" class="mb-2 error" role="alert">
                            {{ valuesErrors.synergy }}
                        </div>
                    </div>

                    <div v-if="canHasSynergyMax">
                        <p>Суммируется, но максимальный размер:</p>
                        <v-select
                            v-model="values.maxValueType"
                            :options="[{text: 'Без ограничения', value: null}, ...discountSizeTypes]"
                            :error="valuesErrors.synergyMaxValueType"
                            @change="initErrorSynergyMaxValueType"
                        >Тип значения
                        </v-select>
                        <v-input
                            v-model="values.maxValue"
                            type="number"
                            min="1"
                            :error="valuesErrors.synergyMaxValue"
                            @change="initErrorSynergyMaxValue"
                        >Значение в {{ isPercentType(values.value_type) ? 'процентах' : 'рублях' }}</v-input>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import VSelect from "../../../../components/controls/VSelect/VSelect.vue";
    import BrandsSearch from '../../components/brands-search.vue';
    import CategoriesSearch from '../../components/categories-search.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
    import VSelect2 from "../../../../components/controls/VSelect2/v-select2.vue";

    export default {
        components: {
            VSelect2,
            VInput,
            VSelect,
            FMultiSelect,
            CategoriesSearch,
            BrandsSearch,
        },

        model: {
            prop: 'value',
            event: 'input'
        },

        props: {
            discount: {
                type: Object,
                required: true,
            },

            types: {
                type: Object,
                required: true
            },

            value: {
                type: Object,
                default() {
                    return {
                        type: null,
                    };
                }
            },

            iPaymentMethods: {
                type: Array,
                default: () => []
            },

            iDeliveryMethods: {
                type: Array,
                default: () => []
            },

            regions: {
                type: Array,
                default: () => [],
            },

            brands: {
                type: Array,
                default: () => [],
            },

            categories: {
                type: Array,
                default: () => [],
            },

            segments: {
                type: Array,
                default: () => [],
            },

            roles: {
                type: Array,
                default: () => [],
            },

            discounts: {
                type: Array,
                default: () => []
            }
        },

        data() {
            return {
                conditionType: this.value.type,
                values: this.createValuesFromProperty(this.value),

                conditionError: null,

                valuesErrors: {
                    sum: null,
                    brands: null,
                    categories: null,
                    count: null,
                    offer: null,
                    deliveryMethods: null,
                    paymentMethods: null,
                    regions: null,
                    user: null,
                    sequenceNumber: null,
                    synergy: null,
                    synergyMaxValueType: null,
                    synergyMaxValue: null,
                },

                CONDITION_TYPE_FIRST_ORDER: 1,
                CONDITION_TYPE_MIN_PRICE_ORDER: 2,
                CONDITION_TYPE_MIN_PRICE_BRAND: 3,
                CONDITION_TYPE_MIN_PRICE_CATEGORY: 4,
                CONDITION_TYPE_EVERY_UNIT_PRODUCT: 5,
                CONDITION_TYPE_DELIVERY_METHOD: 6,
                CONDITION_TYPE_PAY_METHOD: 7,
                CONDITION_TYPE_REGION: 8,
                CONDITION_TYPE_USER: 9,
                CONDITION_TYPE_ORDER_SEQUENCE_NUMBER: 10,
                CONDITION_TYPE_DISCOUNT_SYNERGY: 11,
            };
        },

        computed: {
            discountSizeTypes() {
                return [
                    {text: 'Проценты', value: 1},
                    {text: 'Рубли', value: 2}
                ];
            },

            allowedTypes() {
                const { types } = this;

                let result = {};

                for (const key in types) {
                    const index = this.discount.conditions.findIndex(condition => condition.type === types[key].value);

                    if (index === -1 || this.value.type === types[key].value) {
                        result[key] = types[key];
                    }
                }

                return result;
            },

            valuesUserError() {
                return (this.valuesErrors.user) ? ' ' : null;
            },

            canHasSynergyMax() {
                if ([
                    this.discountTypes.brand,
                    this.discountTypes.category,
                    this.discountTypes.offer,
                ].indexOf(this.discount.type) === -1) {
                    return false;
                }

                let canHasSynergyMax = true;

                if (this.values.synergy.length > 0) {
                    this.values.synergy.forEach((discount_id) => {
                        let discount = this.discounts.find(discount => discount.value === discount_id);
                        if (discount) {
                            if ([
                                this.discountTypes.brand,
                                this.discountTypes.category,
                                this.discountTypes.offer,
                            ].indexOf(discount.type) === -1) {
                                canHasSynergyMax = false;
                            }
                        }
                    });
                }

                return canHasSynergyMax;
            }
        },

        watch: {
            conditionType() {
                this.initConditionError();
                this.initSumError();
                this.initBrandsError();

                this.values = Object.assign(
                    this.createValuesFromProperty(this.value),
                    {
                        synergy: [],
                        roles: [],
                        segments: [],
                        regions: [],
                        deliveryMethods: [],
                        paymentMethods: [],
                        users: ''
                    }
                );
            },

            values() {
                this.$emit('input', Object.assign(this.values, {
                    type: this.conditionType
                }));
            },

            'values.users': {
                handler(val, oldVal) {
                    if (val && val !== oldVal) {
                        let format = this.formatIds(this.values.users).join(', ');
                        let separator = val.slice(-1) === ','
                            ? ','
                            : (val.slice(-2) === ', ' ? ', ' : '');

                        this.values.users = format + separator;
                    }
                },
            },
        },

        methods: {
            createValuesFromProperty(property) {
                const keys = Object.keys(property).filter(key => {
                    return key !== 'type';
                });

                const inputObject = keys.reduce((summary, currentKey) => {
                    return Object.assign(summary, {
                        [currentKey]: property[currentKey]
                    })
                }, {});

                return Object.assign({
                    synergy: [],
                    roles: [],
                    segments: [],
                    regions: [],
                    deliveryMethods: [],
                    paymentMethods: [],
                    users: ''
                }, inputObject);
            },

            formatIds(ids) {
                if (!ids) {
                    return [];
                }

                return ids
                    .split(',')
                    .map(id => {
                        return parseInt(id);
                    })
                    .filter(id => {
                        return id > 0
                    });
            },

            isPercentType(type) {
                return type === 1;
            },

            updateBrandList(brands) {
                this.values = {...this.values, brands};

                this.initBrandsError();
            },

            updateCategoriesList(categories) {
                this.values = {...this.values, categories};

                this.initCategoriesError();
            },

            validate() {
                let bool = true;

                bool = this.checkConditionType() && bool;

                switch (this.conditionType) {
                    case this.CONDITION_TYPE_FIRST_ORDER:
                        break;
                    case this.CONDITION_TYPE_MIN_PRICE_ORDER:
                        bool = this.checkValuesSum() && bool;
                        break;
                    case this.CONDITION_TYPE_MIN_PRICE_BRAND:
                        bool = this.checkValuesSum() && bool;
                        bool = this.checkValuesBrands() && bool;
                        break;
                    case this.CONDITION_TYPE_MIN_PRICE_CATEGORY:
                        bool = this.checkValuesSum() && bool;
                        bool = this.checkValuesCategories() && bool;
                        break;
                    case this.CONDITION_TYPE_EVERY_UNIT_PRODUCT:
                        bool = this.checkValuesCount() && bool;
                        bool = this.checkValuesOffer() && bool;
                        break;
                    case this.CONDITION_TYPE_DELIVERY_METHOD:
                        bool = this.checkValuesDeliveryMethods() && bool;
                        break;
                    case this.CONDITION_TYPE_PAY_METHOD:
                        bool = this.checkValuesPaymentMethods() && bool;
                        break;
                    case this.CONDITION_TYPE_REGION:
                        bool = this.checkValuesRegions() && bool;
                        break;
                    case this.CONDITION_TYPE_USER:
                        bool = this.checkValuesUser() && bool;
                        break;
                    case this.CONDITION_TYPE_ORDER_SEQUENCE_NUMBER:
                        bool = this.checkValuesSequenceNumber() && bool;
                        break;
                    case this.CONDITION_TYPE_DISCOUNT_SYNERGY:
                        bool = this.checkValuesSynergy() && bool;
                        bool = this.checkValuesSynergyMax() && bool;
                        break;
                }

                return bool;
            },

            checkConditionType() {
                if (!(this.conditionType)) {
                    this.conditionError = "Обязательное поле!";
                    return false;
                }

                return true;
            },

            checkValuesSum() {
                if (!(this.values.sum)) {
                    this.valuesErrors.sum = "Обязательное поле!";
                    return false;
                } else {
                    if (!(this.values.sum > 0)) {
                        this.valuesErrors.sum = "Значение должно быть больше 0!";
                        return false;
                    }
                }
                return true;
            },

            checkValuesBrands() {
                if (!(this.values.brands && this.values.brands.length > 0)) {
                    this.valuesErrors.brands = "Выберите хотя бы один бренд!";
                    return false;
                }
                return true;
            },

            checkValuesCategories() {
                if (!(this.values.categories && this.values.categories.length > 0)) {
                    this.valuesErrors.categories = "Выберите хотя бы одну категорию!";
                    return false;
                }
                return true;
            },

            checkValuesCount() {
                if (!(this.values.count)) {
                    this.valuesErrors.count = "Обязательное поле!";
                    return false;
                } else {
                    if (!(this.values.count > 0)) {
                        this.valuesErrors.count = "Значение должно быть больше 0!";
                        return false;
                    }
                }
                return true;
            },

            checkValuesOffer() {
                if (!(this.values.offer)) {
                    this.valuesErrors.offer = "Обязательное поле!";
                    return false;
                } else {
                    if (!(this.values.offer > 0)) {
                        this.valuesErrors.offer = "ID товара должен быть больше 0";
                        return false;
                    }
                }
                return true;
            },

            checkValuesDeliveryMethods() {
                if (!(this.values.deliveryMethods.length > 0)) {
                    this.valuesErrors.deliveryMethods = "Выберите хотя бы один способ доставки";
                    return false;
                }
                return true;
            },

            checkValuesPaymentMethods() {
                if (!(this.values.paymentMethods.length > 0)) {
                    this.valuesErrors.paymentMethods = "Выберите хотя бы один способ оплаты";
                    return false;
                }
                return true;
            },

            checkValuesRegions() {
                if (!(this.values.regions.length > 0)) {
                    this.valuesErrors.regions = "Выберите хотя бы один регион";
                    return false;
                }
                return true;
            },

            checkValuesUsers() {
                return (this.formatIds(this.values.users).length > 0);
            },

            checkValuesRoles() {
                return (this.values.roles.length > 0);
            },

            checkValuesSegments() {
                return (this.values.segments.length > 0);
            },

            checkValuesUser() {
                if (!(this.checkValuesUsers() || this.checkValuesRoles() || this.checkValuesSegments())) {
                    this.valuesErrors.user = "Укажите ID пользователей, или выберите сегменты, или выберите роли пользователей";
                    return false;
                }
                return true;
            },

            checkValuesSequenceNumber() {
                if (!(this.values.sequenceNumber)) {
                    this.valuesErrors.sequenceNumber = "Обязательное поле!";
                    return false;
                } else {
                    if (!(this.values.sequenceNumber > 0)) {
                        this.valuesErrors.sequenceNumber = "Порядковый номер заказа должен быть больше 0";
                        return false;
                    }
                }
                return true;
            },

            checkValuesSynergy() {
                if (!(this.values.synergy.length > 0)) {
                    this.valuesErrors.synergy = "Выберите хотя бы одну другую скидку";
                    return false;
                }
                return true;
            },

            checkValuesSynergyMax() {
                if (!this.canHasSynergyMax) {
                    this.values.maxValue = null;
                    this.values.maxValueType = null;
                    return true;
                }

                if (!this.values.maxValue && !this.values.maxValueType) {
                    return true;
                } else if (this.values.maxValue && this.values.maxValueType) {
                    switch (this.values.maxValueType) {
                        case 1:
                            if (!(this.values.maxValue > 0 && this.values.maxValue <= 100)) {
                                this.valuesErrors.synergyMaxValue = 'Значение должно быть в диапазоне от 1 до 100!';
                                return false;
                            }
                            break;
                        case 2:
                            if (!(this.values.maxValue > 0)) {
                                this.valuesErrors.synergyMaxValue = 'Значение должно быть больше 0!';
                                return false;
                            }
                            break;
                    }
                    return true;
                } else if (this.values.maxValue) {
                    this.valuesErrors.synergyMaxValueType = 'Выберите тип максимального значения';
                    return false;
                } else if (this.values.maxValueType) {
                    this.valuesErrors.synergyMaxValue = 'Введите максимальное значение';
                    return false;
                }
                return false;
            },

            initConditionError() {
                this.conditionError = null;
            },

            initSumError() {
                this.valuesErrors.sum = null;
            },

            initBrandsError() {
                this.valuesErrors.brands = null;
            },

            initCategoriesError() {
                this.valuesErrors.categories = null;
            },

            initCountError() {
                this.valuesErrors.count = null;
            },

            initOfferError() {
                this.valuesErrors.offer = null;
            },

            initDeliveryMethodsError() {
                this.valuesErrors.deliveryMethods = null;
            },

            initPaymentMethodsError() {
                this.valuesErrors.paymentMethods = null;
            },

            initRegionsError() {
                this.valuesErrors.regions = null;
            },

            initUserError() {
                this.valuesErrors.user = null;
            },

            initSequenceNumberError() {
                this.valuesErrors.sequenceNumber = null;
            },

            initSynergyError() {
                this.valuesErrors.synergy = null;
            },

            initErrorSynergyMaxValueType() {
                this.valuesErrors.synergyMaxValueType = null;
            },

            initErrorSynergyMaxValue() {
                this.valuesErrors.synergyMaxValue = null;
            },
        }
    };
</script>
