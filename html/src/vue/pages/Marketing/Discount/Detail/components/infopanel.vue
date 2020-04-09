<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="4">
                Инфопанель
                <button class="btn btn-success btn-sm" @click="save" :disabled="!showBtn">
                    Сохранить
                </button>
                <button @click="cancel" class="btn btn-outline-danger btn-sm" :disabled="!showBtn">Отмена</button>
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
                    <input class="form-control form-control-sm" id="discount-name-input" v-model="discount.name"/>
                </td>
            </tr>
            <tr>
                <th><label for="discount-value-input">Размер</label></th>
                <td>
                    <input class="form-control form-control-sm" id="discount-value-input" v-model="discount.value"/>
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
                    <select class="form-control form-control-sm" id="discount-type-select" v-model="discount.type">
                        <option v-for="type in discountTypes" :value="type.value">
                            {{ type.text }}
                        </option>
                    </select>
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
                        <select class="form-control form-control-sm" id="discount-merchant-select" v-model="discount.merchant_id">
                            <option v-for="merchant in merchants" :value="merchant.id">
                                {{ merchant.name }}
                            </option>
                        </select>
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
    export default {
        name: 'discount-detail-infopanel',
        components: {

        },
        mixins: [],
        props: {
            model: Object,
            discountTypes: Object,
            discountStatuses: Object,
            merchants: Array,
            author: Object,
        },
        data() {
            return {
                showBtn: false,
                merchantBtn: false,
            };
        },
        methods: {
            save() {

            },
            cancel() {

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
        },
        mounted() {
            this.merchantBtn = this.discount.merchant_id > 0;
        },
    };
</script>

