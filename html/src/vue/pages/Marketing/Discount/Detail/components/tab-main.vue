<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="5">
                Основная информация
                <button @click="save" class="btn btn-success" :disabled="!showBtn">Сохранить</button>
                <button @click="cancel" class="btn btn-outline-danger" :disabled="!showBtn">Отмена</button>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Тип</th>
            <th>Условие</th>
            <th colspan="3">Значение</th>
        </tr>
        <tr v-for="condition in discount.conditions">
                <td>{{ condition.type }}</td>
                <td>{{ iConditionTypes[condition.type].text }}</td>
            <template v-if="condition.type === CONDITION_TYPE_FIRST_ORDER">
                <td colspan="3">+</td>
            </template>
            <template v-else-if="condition.type === CONDITION_TYPE_MIN_PRICE_ORDER">
                <td>{{ condition.sum }} ₽</td>
                <td colspan="2">&nbsp;</td>
            </template>
            <template v-else-if="condition.type === CONDITION_TYPE_USER">
                <td>
                    <b>ID пользователей</b>
                    <p v-for="id in condition.users" class="condition-list">{{ id }}</p>
                    <p v-if="condition.users.length <= 0">Все</p>
                </td>
                <td>
                    <b>Роли</b>
                    <p v-for="id in condition.roles" class="condition-list">{{ roleName(id) }}</p>
                    <p v-if="condition.roles.length <= 0">Все</p>
                </td>
                <td>
                    <b>Сегменты</b>
                    <p v-for="id in condition.segments" class="condition-list">{{ segmentName(id) }}</p>
                    <p v-if="condition.segments.length <= 0">Все</p>
                </td>
            </template>
            <template v-else-if="condition.type === CONDITION_TYPE_MIN_PRICE_BRAND">
                <td>
                    от {{ condition.sum }} ₽
                </td>
                <td colspan="2">
                    <b>На бренды</b>
                    <p v-for="id in condition.brands" class="condition-list">{{ brandName(id) }}</p>
                </td>
            </template>
            <template v-else-if="condition.type === CONDITION_TYPE_MIN_PRICE_CATEGORY">
                <td>от {{ condition.sum }} ₽</td>
                <td colspan="2">
                    <b>На категории</b>
                    <p v-for="id in condition.categories" class="condition-list">{{ categoryName(id) }}</p>
                </td>
            </template>
            <template v-else-if="condition.type === CONDITION_TYPE_EVERY_UNIT_PRODUCT">
                <td>{{ condition.count }}</td>
                <td colspan="2">&nbsp;</td>
            </template>
            <template v-else-if="condition.type === CONDITION_TYPE_DELIVERY_METHOD">
                <td><p v-for="id in condition.deliveryMethods" class="condition-list">{{ deliveryMethodName(id) }}</p></td>
                <td colspan="2">&nbsp;</td>
            </template>
            <template v-else-if="condition.type === CONDITION_TYPE_PAY_METHOD">
                <td><p v-for="id in condition.paymentMethods" class="condition-list">{{ paymentMethodName(id) }}</p></td>
                <td colspan="2">&nbsp;</td>
            </template>
            <template v-else-if="condition.type === CONDITION_TYPE_REGION">
                <td><p v-for="id in condition.regions" class="condition-list">{{ regionName(id) }}</p></td>
                <td colspan="2">&nbsp;</td>
            </template>
            <template v-else-if="condition.type === CONDITION_TYPE_ORDER_SEQUENCE_NUMBER">
                <td>Каждый {{ condition.sequenceNumber }}-й заказ</td>
                <td colspan="2">&nbsp;</td>
            </template>
            <template v-else-if="condition.type === CONDITION_TYPE_DISCOUNT_SYNERGY">
                <td>
                    <p v-for="id in condition.synergy" class="condition-list">
                        <a :href="getRoute('discount.detail', {id})" target="_blank">{{ discountName(id) }}</a>
                    </p>
                </td>
                <td colspan="2">&nbsp;</td>
            </template>

            <template v-else>
                <td colspan="3">&nbsp;</td>
            </template>
        </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        name: 'tab-main',
        components: {},
        props: {
            model: Object,
            discounts: Array,
            iDiscount: Object,
            iConditionTypes: Object,
            deliveryMethods: Array,
            paymentMethods: Array,
            regions: Array,
            brands: Array,
            categories: Array,
            segments: Array,
            roles: Array,
        },
        data() {
            return {
                showBtn: false,

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
        computed: {
            discount: {
                get() {return this.model},
                set(value) {this.$emit('update:discount', value)},
            },
        },
        methods: {
            save() {

            },
            cancel() {

            },
            optionName(id, options) {
                let option = options.find(o => o.value === id);
                return option ? option.text : 'N/A';
            },
            deliveryMethodName(id) {
                return this.optionName(id, this.deliveryMethods);
            },
            paymentMethodName(id) {
                return this.optionName(id, this.paymentMethods);
            },
            discountName(id) {
                return '#' + id + ' ' + this.optionName(id, this.discounts);
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
        },
        mounted() {

        },
        created() {

        }
    };
</script>

<style scoped>
    .condition-list {
        line-height: 0.9;
        margin-top: 10px;
    }
</style>
