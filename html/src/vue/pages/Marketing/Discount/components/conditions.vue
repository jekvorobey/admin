<template>
    <div class="row">
        <div class="col-12 mt-4" v-if="conditions.length > 0">
            <b>Условия предоставления скидки</b>
            <table class="table table-bordered mt-2">
                <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Условие</th>
                    <th>Значение</th>
                    <th>Действие</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(condition, index) in conditions">
                    <td>{{ index + 1 }}</td>
                    <td>{{ iConditionTypes[condition.type].text }}</td>
                    <td>
                        <template v-if="condition.type === CONDITION_TYPE_MIN_PRICE_ORDER">
                            от {{ condition.sum }} руб.
                        </template>

                        <template v-if="condition.type === CONDITION_TYPE_MIN_PRICE_BRAND">
                            На бренды
                            <ul>
                                <li v-for="id in condition.brands">{{ brandName(id) }}</li>
                            </ul>
                            от {{ condition.sum }} руб.
                        </template>

                        <template v-if="condition.type === CONDITION_TYPE_MIN_PRICE_CATEGORY">
                            На категории
                            <ul>
                                <li v-for="id in condition.categories">{{ categoryName(id) }}</li>
                            </ul>
                            от {{ condition.sum }} руб.
                        </template>

                        <template v-if="condition.type === CONDITION_TYPE_DELIVERY_METHOD">
                            На способы доставки
                            <ul>
                                <li v-for="id in condition.deliveryMethods">{{ deliveryMethodName(id) }}</li>
                            </ul>
                        </template>

                        <template v-if="condition.type === CONDITION_TYPE_PAY_METHOD">
                            На способы оплаты
                            <ul>
                                <li v-for="id in condition.paymentMethods">{{ paymentMethodName(id) }}</li>
                            </ul>
                        </template>

                        <template v-if="condition.type === CONDITION_TYPE_EVERY_UNIT_PRODUCT">
                            Оффер {{ condition.offer }}, количество {{ condition.count }} шт.
                        </template>

                        <template v-if="condition.type === CONDITION_TYPE_REGION">
                            На регионы
                            <ul>
                                <li v-for="id in condition.regions">{{ regionName(id) }}</li>
                            </ul>
                        </template>

                        <template v-if="condition.type === CONDITION_TYPE_USER">
                            <template v-if="condition.users.length > 0">
                                Пользователи:
                                <ul>
                                    <li v-for="id in condition.users">{{ id }}</li>
                                </ul>
                            </template>

                            <template v-if="condition.segments.length > 0">
                                Сегменты:
                                <ul>
                                    <li v-for="id in condition.segments">{{ segmentName(id) }}</li>
                                </ul>
                            </template>

                            <template v-if="condition.roles.length > 0">
                                Роли:
                                <ul>
                                    <li v-for="id in condition.roles">{{ roleName(id) }}</li>
                                </ul>
                            </template>
                        </template>

                        <template v-if="condition.type === CONDITION_TYPE_ORDER_SEQUENCE_NUMBER">
                            {{ condition.sequenceNumber }}-й заказ Клиента
                        </template>

                        <template v-if="condition.type === CONDITION_TYPE_DISCOUNT_SYNERGY">
                            Суммируется со скидками:
                            <ul>
                                <li v-for="id in condition.synergy">{{ discountName(id) }}</li>
                            </ul>
                            <template v-if="condition.maxValueType">
                                Максимальный размер (Значение в {{ isPercentType(condition.maxValueType) ? 'процентах' : 'рублях' }}): {{ condition.maxValue }}
                            </template>
                        </template>
                    </td>
                    <td>
                        <button class="btn btn-warning" type="button" @click="editCondition(condition.type)">Редактировать</button><br/>
                        <button class="btn btn-danger mt-3" type="button" @click="deleteCondition(condition.type)">Удалить</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <template v-if="Object.values(conditionTypes).length > 0">
            <v-select :options="conditionTypes"
                      v-model="conditionType"
                      class="col-6 mt-3"
                      :error="conditionError"
                      @change="selectCondition"
            >Условия предоставления скидки</v-select>
            <div class="col-3 mt-3">
                <label class="row">&nbsp;</label>
                <button type="button" class="btn"
                        :class="'btn-warning'"
                        @click="addCondition()">Добавить условие</button>
            </div>
        </template>

        <!-- На заказ от определенной суммы -->
        <div class="col-4" v-if="conditionType === CONDITION_TYPE_MIN_PRICE_ORDER">
            <v-input v-model="values.sum"
                     type="number"
                     min="0"
                     :error="valuesErrors.sum"
                     @change="initSumError"
            >От (руб.)</v-input>
        </div>

        <!-- На заказ от определенной суммы товаров заданного бренда -->
        <template v-if="conditionType === CONDITION_TYPE_MIN_PRICE_BRAND">
            <div class="col-4">
                <v-input v-model="values.sum"
                         type="number"
                         min="0"
                         :error="valuesErrors.sum"
                         @change="initSumError"
                >От (руб.)</v-input>
            </div>

            <BrandsSearch
                    classes="col-12"
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
            <div class="col-4">
                <v-input v-model="values.sum"
                         type="number"
                         min="0"
                         :error="valuesErrors.sum"
                         @change="initSumError"
                >От (руб.)</v-input>
            </div>

            <CategoriesSearch
                    classes="col-12"
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
            <div class="col-4">
                <v-input v-model="values.count"
                         type="number"
                         min="0"
                         :error="valuesErrors.count"
                         @change="initCountError"
                >Количество</v-input>
            </div>

            <div class="col-4">
                <v-input v-model="values.offer"
                         type="number"
                         min="0"
                         :error="valuesErrors.offer"
                         @change="initOfferError"
                >Оффер</v-input>
            </div>
        </template>

        <!-- На способ доставки -->
        <div class="col-4" v-if="conditionType === CONDITION_TYPE_DELIVERY_METHOD">
            <v-select v-model="values.deliveryMethods"
                      :options="deliveryMethods"
                      :multiple="true"
                      :error="valuesErrors.deliveryMethods"
                      @change="initDeliveryMethodsError"
            >Способ доставки</v-select>
        </div>

        <!-- На способ оплаты -->
        <div class="col-4" v-if="conditionType === CONDITION_TYPE_PAY_METHOD">
            <v-select v-model="values.paymentMethods"
                      :options="paymentMethods"
                      :multiple="true"
                      :error="valuesErrors.paymentMethods"
                      @change="initPaymentMethodsError"
            >Способ оплаты</v-select>
        </div>

        <!-- Территория действия (регион с точки зрения адреса доставки заказа) -->
        <div class="col-12" v-if="conditionType === CONDITION_TYPE_REGION">
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

        <div class="mb-2 col-12" :class="{ 'error': valuesErrors.user }">
            {{ valuesErrors.user }}
        </div>
        <div class="col-4" v-if="conditionType === CONDITION_TYPE_USER">
            <!-- Для определенных пользователей системы -->
            <v-input v-model="values.users"
                     :error="valuesUserError"
                     @change="initUserError"
            >ID пользователей (через запятую)</v-input>
            <!-- Для определенных пользовательских сегментов -->
            <v-select v-model="values.segments"
                      :options="segments"
                      :multiple="true"
                      :error="valuesUserError"
                      @change="initUserError"
            >Сегменты</v-select>
            <!-- Для определенных пользовательских ролей -->
            <v-select v-model="values.roles"
                      :options="roles"
                      :multiple="true"
                      :error="valuesUserError"
                      @change="initUserError"
            >Роли</v-select>
        </div>

        <!-- Порядковый номер заказа -->
        <div class="col-4" v-if="conditionType === CONDITION_TYPE_ORDER_SEQUENCE_NUMBER">
            <v-input v-model="values.sequenceNumber"
                     type="number"
                     min="0"
                     :error="valuesErrors.sequenceNumber"
                     @change="initSequenceNumberError"
            >Порядковый номер заказа</v-input>
        </div>

        <!-- Суммируется с другими маркетинговыми инструментами -->
        <div class="col-6" v-if="conditionType === CONDITION_TYPE_DISCOUNT_SYNERGY">
            <v-select v-model="values.synergy"
                      :options="discounts"
                      :multiple="true"
                      :selectSize="10"
                      :error="valuesErrors.synergy"
                      @change="initSynergyError"
            >Суммируется с другими скидками</v-select>

            <div v-if="canHasSynergyMax">
                <p>Суммируется, но максимальный размер:</p>
                <v-select v-model="values.maxValueType"
                        :options="[{text: 'Без ограничения', value: null}, ...discountSizeTypes]"
                        :error="valuesErrors.synergyMaxValueType"
                        @change="initErrorSynergyMaxValueType"
                >Тип значения</v-select>
                <v-input v-model="values.maxValue"
                        type="number"
                        min="1"
                        :error="valuesErrors.synergyMaxValue"
                        @change="initErrorSynergyMaxValue"
                >Значение в {{ isPercentType(values.value_type) ? 'процентах' : 'рублях' }}
                </v-input>
            </div>

        </div>

        <div class="col-12" id="conditions-scroll">&nbsp;</div>
    </div>
</template>

<script>
    import BrandsSearch from './brands-search.vue';
    import CategoriesSearch from './categories-search.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import Services from '../../../../../scripts/services/services';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';

    export default {
        components: {
            CategoriesSearch,
            BrandsSearch,
            VInput,
            VSelect,
            FMultiSelect,
            Services,
        },
        props: {
            discount: Object,
            discounts: Array,
            conditions: Array,
            iConditionTypes: Object,
            paymentMethods: Array,
            deliveryMethods: Array,
            regions: Array,
            brands: Array,
            categories: Array,
            segments: Array,
            roles: Array,
            discountSizeTypes: Array,
        },
        data() {
            return {
                conditionTypes: {...this.iConditionTypes},
                conditionType: null,
                values: {},
                // Тип условия на скидку
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
            }
        },
        methods: {
            addCondition() {
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

                if (bool) {
                    let values = {...this.values};
                    values.users = this.formatIds(values.users);

                    Services.event().$emit('discount-condition-add', {'type': this.conditionType, ...values});
                    this.$nextTick(function () {
                        this.conditionType = null;
                        this.updateConditionTypes();
                    });
                }
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
            editCondition(condType) {
                let values = this.conditions.find(condition => {
                    return condition.type === condType;
                });
                if (values) {
                    values.users = values.users.join(',');
                } else {
                    values = {...this.values};
                }

                let event = Services.event();
                event.$emit('discount-condition-delete', condType);
                this.$nextTick(() => {
                    this.updateConditionTypes();
                    this.conditionType = condType;
                    this.$nextTick(() =>  {
                        this.values = values;
                        event.$emit('set-filter-condition-type-region', this.values.regions);
                        let scrollElem = document.getElementById("conditions-scroll");
                        if (scrollElem) {
                            scrollElem.scrollIntoView({block: "start", behavior: "smooth"});
                        }
                    });
                });
            },
            deleteCondition(condType) {
                Services.event().$emit('discount-condition-delete', condType);
                this.$nextTick(function () {
                    this.updateConditionTypes();
                });
            },
            updateConditionTypes() {
                let conditionTypes = {};
                for (let type in this.iConditionTypes) {
                    if (this.conditions.filter(c => { return c.type == type }).length === 0) {
                        conditionTypes[type] = this.iConditionTypes[type];
                    }
                }
                this.conditionTypes = conditionTypes;
            },
            optionName(id, options) {
                let option = options.find(o => o.value === id);
                return option ? option.text : 'N/A';
            },
            discountName(id) {
                return this.optionName(id, this.discounts);
            },
            deliveryMethodName(id) {
                return this.optionName(id, this.deliveryMethods);
            },
            paymentMethodName(id) {
                return this.optionName(id, this.paymentMethods);
            },
            regionName(id) {
                return this.optionName(id, this.regions);
            },
            roleName(id) {
                return this.optionName(id, this.roles);
            },
            segmentName(id) {
                return this.optionName(id, this.segments);
            },
            brandName(id) {
                let brand = this.brands.find(brand => brand.id === id);
                return brand ? brand.name : 'N/A';
            },
            categoryName(id) {
                let category = this.categories.filter(category => category.id === id);
                return category.length > 0 ? category[0].name : 'N/A';
            },
            updateBrandList(brands) {
                this.values = {...this.values, brands};
                this.initBrandsError();
            },
            updateCategoriesList(categories) {
                this.values = {...this.values, categories};
                this.initCategoriesError();
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
            selectCondition() {
                this.initConditionError();
                this.initSumError();
                this.initBrandsError();
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
            isPercentType(type) {
                return type === 1;
            },
        },
        computed: {
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
                this.values = {
                    synergy: [],
                    roles: [],
                    segments: [],
                    regions: [],
                    deliveryMethods: [],
                    paymentMethods: [],
                    users: ''
                };
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
            conditions() {
                this.updateConditionTypes();
            },
        },
    }
</script>

<style>
    .error {
        color: red;
    }
</style>
