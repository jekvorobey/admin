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
                            Каждый {{ condition.sequenceNumber }}-й заказ
                        </template>

                        <template v-if="condition.type === CONDITION_TYPE_DISCOUNT_SYNERGY">
                            Взаимодействует со скидками:
                            <ul>
                                <li v-for="id in condition.synergy">{{ discountName(id) }}</li>
                            </ul>
                        </template>
                    </td>
                    <td><fa-icon icon="times" title="Удалить" class="cursor-pointer text-danger"
                                 @click="deleteCondition(condition.type)"></fa-icon></td>
                </tr>
                </tbody>
            </table>
        </div>

        <template v-if="Object.values(conditionTypes).length > 0">
            <v-select :options="conditionTypes" v-model="conditionType" class="col-6 mt-3">Условия предоставления скидки</v-select>
            <div class="col-3 mt-3">
                <label class="row">&nbsp;</label>
                <button type="button btn-inline" class="btn"
                        :class="valid ? 'btn-warning' : 'btn-light'"
                        :disabled="!valid"
                        @click="addCondition()">Добавить условие</button>
            </div>
        </template>

        <!-- На заказ от определенной суммы -->
        <div class="col-4" v-if="conditionType === CONDITION_TYPE_MIN_PRICE_ORDER">
            <v-input v-model="values.sum" type="number" min="0">От (руб.)</v-input>
        </div>

        <!-- На заказ от определенной суммы товаров заданного бренда -->
        <template v-if="conditionType === CONDITION_TYPE_MIN_PRICE_BRAND">
            <div class="col-4">
                <v-input v-model="values.sum" type="number" min="0">От (руб.)</v-input>
            </div>

            <BrandsSearch
                    classes="col-12"
                    title="Бренды"
                    key="brands-search-new-condition"
                    :brands="brands"
                    @update="updateBrandList"
            ></BrandsSearch>
        </template>

        <!-- На заказ от определенной суммы товаров заданной категории -->
        <template v-if="conditionType === CONDITION_TYPE_MIN_PRICE_CATEGORY">
            <div class="col-4">
                <v-input v-model="values.sum" type="number" min="0">От (руб.)</v-input>
            </div>

            <CategoriesSearch
                    classes="col-12"
                    title="Категории"
                    key="categories-search-new-condition"
                    :categories="categories"
                    @update="updateCategoriesList"
            ></CategoriesSearch>
        </template>

        <!-- На количество единиц одного товара -->
        <template v-if="conditionType === CONDITION_TYPE_EVERY_UNIT_PRODUCT">
            <div class="col-4">
                <v-input v-model="values.count" type="number" min="0">Количество</v-input>
            </div>

            <div class="col-4">
                <v-input v-model="values.offer" type="number" min="0">Оффер</v-input>
            </div>
        </template>

        <!-- На способ доставки -->
        <div class="col-4" v-if="conditionType === CONDITION_TYPE_DELIVERY_METHOD">
            <v-select v-model="values.deliveryMethods" :options="deliveryMethods" :multiple="true">Способ доставки</v-select>
        </div>

        <!-- На способ оплаты -->
        <div class="col-4" v-if="conditionType === CONDITION_TYPE_PAY_METHOD">
            <v-select v-model="values.paymentMethods" :options="paymentMethods" :multiple="true">Способ оплаты</v-select>
        </div>

        <!-- Территория действия (регион с точки зрения адреса доставки заказа) -->
        <div class="col-12" v-if="conditionType === CONDITION_TYPE_REGION">
            <f-multi-select
                    v-model="values.regions"
                    :options="regions"
                    grouped
                    multiple>
                Регионы
            </f-multi-select>
        </div>

        <div class="col-4" v-if="conditionType === CONDITION_TYPE_USER">
            <!-- Для определенных пользователей системы -->
            <v-input v-model="values.users">ID пользователей (через запятую)</v-input>
            <!-- Для определенных пользовательских сегментов -->
            <v-select v-model="values.segments"
                      :options="segments"
                      :multiple="true"
            >Сегменты</v-select>
            <!-- Для определенных пользовательских ролей -->
            <v-select v-model="values.roles"
                      :options="roles"
                      :multiple="true"
            >Роли</v-select>
        </div>

        <!-- Порядковый номер заказа -->
        <div class="col-4" v-if="conditionType === CONDITION_TYPE_ORDER_SEQUENCE_NUMBER">
            <v-input v-model="values.sequenceNumber" type="number" min="0">Порядковый номер заказа</v-input>
        </div>

        <!-- Взаимодействия с другими маркетинговыми инструментами -->
        <div class="col-6" v-if="conditionType === CONDITION_TYPE_DISCOUNT_SYNERGY">
            <v-select v-model="values.synergy"
                      :options="discounts"
                      :multiple="true"
                      :selectSize="10"
            >Взаимодействие с другими скидками</v-select>
        </div>
    </div>
</template>

<script>
    import BrandsSearch from './brands-search.vue';
    import CategoriesSearch from './categories-search.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import Services from "../../../../../scripts/services/services";
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';

    export default {
        components: {
            CategoriesSearch,
            BrandsSearch,
            VInput,
            VSelect,
            FMultiSelect,
        },
        props: {
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
            }
        },
        methods: {
            addCondition() {
                let values = {...this.values};
                values.users = this.formatIds(values.users);

                Services.event().$emit('discount-condition-add', {'type': this.conditionType, ...values});
                this.$nextTick(function () {
                    this.conditionType = null;
                    this.updateConditionTypes();
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
            },
            updateCategoriesList(categories) {
                this.values = {...this.values, categories};
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
            valid() {
                switch (this.conditionType) {
                    case this.CONDITION_TYPE_FIRST_ORDER:
                        return true;
                    case this.CONDITION_TYPE_MIN_PRICE_ORDER:
                        return this.values.sum > 0;
                    case this.CONDITION_TYPE_MIN_PRICE_BRAND:
                        return this.values.sum > 0 && this.values.brands && this.values.brands.length > 0;
                    case this.CONDITION_TYPE_MIN_PRICE_CATEGORY:
                        return this.values.sum > 0 && this.values.categories && this.values.categories.length > 0;
                    case this.CONDITION_TYPE_EVERY_UNIT_PRODUCT:
                        return this.values.count > 0 && this.values.offer > 0;
                    case this.CONDITION_TYPE_DELIVERY_METHOD:
                        return this.values.deliveryMethods.length > 0;
                    case this.CONDITION_TYPE_PAY_METHOD:
                        return this.values.paymentMethods.length > 0;
                    case this.CONDITION_TYPE_REGION:
                        return this.values.regions.length > 0;
                    case this.CONDITION_TYPE_USER:
                        return this.formatIds(this.values.users).length > 0 || this.values.roles.length > 0 || this.values.segments.length;
                    case this.CONDITION_TYPE_ORDER_SEQUENCE_NUMBER:
                        return this.values.sequenceNumber > 0;
                    case this.CONDITION_TYPE_DISCOUNT_SYNERGY:
                        return this.values.synergy.length > 0;
                }
                return false;
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
