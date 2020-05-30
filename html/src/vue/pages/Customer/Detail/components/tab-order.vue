<template>
    <table class="table table-sm" v-if="orders && orders.length > 0">
        <thead>
        <tr>
            <th colspan="14">Заказы</th>
        </tr>
        <tr>
            <th>№ заказа</th>
            <th>Дата оформления</th>
            <th>Сумма</th>
            <th>Оплата</th>
            <th>Способ оплаты</th>
            <th>Способ доставки</th>
            <th>Стоимость доставки</th>
            <th>Тип доставки</th>
            <th>Кол-во доставок</th>
            <th>Служба доставки</th>
            <th>Статус</th>
            <th>Дата доставки</th>
            <th>Комментарий</th>
            <th>Дата последнего изменения</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="order in orders">
            <td><a :href="getRoute('orders.detail', {id: order.id})">{{ order.number }}</a></td>
            <td>{{ datetimePrint(order.created_at) }}</td>
            <td>{{ order.price }}</td>
            <td><fa-icon :icon="order.isPayed ? 'check' : 'times'" :class="order.isPayed ? 'text-success': 'text-danger'"/></td>
            <td>{{ order.paymentMethod || '-' }}</td>
            <td>{{ order.deliveryMethod || '-' }}</td>
            <td>{{ order.delivery_cost }}</td>
            <td>{{ order.deliveryType.name }}</td>
            <td>{{ order.deliveryCount }}</td>
            <td>{{ order.deliverySystems || '-' }}</td>
            <td><order-status :status='order.status'/></td>
            <td>{{ order.deliveryDate }}</td>
            <td>{{ order.manager_comment }}</td>
            <td>{{ order.updated_at }}</td>
        </tr>
        </tbody>
    </table>
    <p v-else class="text-center p-3">Заказов не найдено!</p>
</template>

<script>
    import Services from '../../../../../scripts/services/services.js';

    export default {
    name: 'tab-order',
    props: ['id'],
    data() {
        return {
            orders: [],
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.order', {id: this.id})).then(data => {
            this.orders = data.orders;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>