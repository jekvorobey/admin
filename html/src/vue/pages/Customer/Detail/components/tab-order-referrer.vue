<template>
    <div>
        <a :href="getRoute('customers.detail.orderReferrer.export', {id: this.id})" class="btn btn-info btn-sm">
            <fa-icon icon="file-excel"/>
        </a>
        <table class="table">
        <thead>
            <tr>
                <th>Номер заказа</th>
                <th>Товар заказа</th>
                <th>Кол-во</th>
                <th>Размер вознаграждения</th>
                <th>Дата заказа</th>
                <th>Источник</th>
                <th>ID реферала</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="order in orders">
                <td>{{ order.order_number }}</td>
                <td>{{ order.name }}</td>
                <td>{{ order.qty | integer}}</td>
                <td>{{ cutValue(order.price_commission) }}</td>
                <td>{{ order.order_date }}</td>
                <td>{{ order.source_name }}</td>
                <td>{{ order.customer_id }}</td>
                <td>
                    <v-delete-button @delete="deleteOrderHistory(order.id)" btn-class="btn-danger btn-sm"/>
                </td>
            </tr>
        </tbody>
        </table>
    </div>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import Helpers from "../../../../../scripts/helpers.js";

export default {
    name: 'tab-order-referrer',
    components: {VDeleteButton},
    props: ['id'],
    data() {
        return {
            orders: [],
        }
    },
    methods: {
        cutValue(value) {
            let cut = parseFloat(value).toFixed(2);
            return cut.toString().replace('.', ',');
        },
        deleteOrderHistory(order_id) {
            Services.showLoader();
            Services.net().delete(this.getRoute('customers.detail.orderReferrer.delete', {
                id: this.id,
                history_id: order_id,
            })).then(data => {
                this.orders = data.orders;
            }).finally(() => {
                Services.hideLoader();
            })
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.orderReferrer', {id: this.id})).then(data => {
            this.orders = data.orders;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>