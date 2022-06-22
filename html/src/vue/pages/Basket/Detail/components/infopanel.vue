<template>
    <b-card>
        <b-row class="mb-3">
            <div class="col-sm-6">
                <span class="font-weight-bold">Корзина {{ basket.id }}</span><br>
                <order-type :type='basket.type'/><br>
                <span>Создана {{ basket.created_at }}</span><br>
                <span>Последнее обновление {{ basket.updated_at }}</span>
            </div>
        </b-row>
        <b-row>
            <div class="col-sm-6">
                <span class="font-weight-bold">Покупатель:</span>
                <span v-if="basket.customer && basket.customer.user && canView(blocks.clients)">
                  <a :href="getRoute('customers.detail', {id: basket.customer_id})" target="_blank">
                      {{ basket.customer.user.full_name ? basket.customer.user.full_name : basket.customer.user.login }}
                  </a>
                </span>
                <span v-else-if="basket.customer && basket.customer.user">
                  {{ basket.customer.user.full_name ? basket.customer.user.full_name : basket.customer.user.login }}
                </span>
                <span v-else>N/A</span>
            </div>
        </b-row>
        <b-row class="mb-3">
            <div class="col-sm-6">
                <span class="font-weight-bold">Телефон:</span>
                  <a :href="customerPhoneLink" target="_blank"
                     v-if="basket.customer && basket.customer.user">{{ basket.customer.user.phone }}
                  </a>
                <span v-else>N/A</span>
            </div>
        </b-row>
        <b-row>
            <div class="col-sm-6">
                <span class="font-weight-bold">Сумма корзины:</span> {{ basket.total_price }} руб.
            </div>
        </b-row>
        <b-row>
            <div class="col-sm-6">
                <span class="font-weight-bold">Кол-во ед. товара:</span> {{ basket.total_qty }} шт.
            </div>
        </b-row>
        <b-row v-if="isProductType">
            <div class="col-sm-6">
                <span class="font-weight-bold">Вес заказа:</span> {{ basket.weight }} г.
            </div>
        </b-row>
    </b-card>
</template>

<script>
export default {
    name: 'infopanel',
    props: [
        'model',
    ],
    methods: {

    },
    computed: {
        basket: {
            get() {
                return this.model
            },
            set(value) {
                this.$emit('update:model', value)
            },
        },
        isProductType() {
            return this.basket.type == this.basketTypes.product;
        },
        customerPhoneLink() {
            return 'tel:' + (this.basket.customer && this.basket.customer.user ? this.basket.customer.user.phone : '');
        },
    },
};
</script>
