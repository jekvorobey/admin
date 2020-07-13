<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="4">
                Инфопанель
                <button class="btn btn-success btn-sm" @click="save">
                    Сохранить
                </button>
                <button @click="cancel" class="btn btn-outline-danger btn-sm" :disabled="true">Отмена</button>
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
                        <option v-for="type in optionDiscountTypes" :value="type.value">
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
    import Services from "../../../../../../scripts/services/services";

    export default {
        name: 'discount-detail-infopanel',
        components: {

        },
        mixins: [],
        props: {
            model: Object,
            optionDiscountTypes: Object,
            discountStatuses: Object,
            merchants: Array,
            author: Object,
        },
        data() {
            return {
                merchantBtn: false,
            };
        },
        methods: {
            save() {
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
                    this.result = (data.status === 'ok') ? success : err;
                    this.openModal('UpdateDiscount');
                    this.processing = false;
                    this.setTimeout(location=this.getRedirectRoute(discount.type), 4000);
                }, () => {
                    this.result = err;
                    this.openModal('UpdateDiscount');
                }).finally(() => {
                    this.processing = false;
                    Services.hideLoader();
                });
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

