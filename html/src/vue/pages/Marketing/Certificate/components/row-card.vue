<template>
    <tr>
        <td><a :href="getRoute('orders.detail', {id: request.order_id})">{{ request.order_number }}</a></td>
        <td>{{ card.id }}</td>
        <td>{{ card.pin }}</td>
        <td>{{ card.price.toLocaleString() }}</td>
        <td>{{ card.balance.toLocaleString() }}</td>
        <td>{{ card.created_at | datetime }}</td>
        <td>{{ request.notified_at | datetime }}</td>
        <td>{{ card.activated_at | datetime }}</td>
        <td><card-status :status="card.status"/></td>
        <td><a v-if="customer" :href="customer.url">{{ customer.name }}</a></td>
        <td><a v-if="recipient" :href="recipient.url">{{ recipient.name }}</a></td>
    </tr>
</template>

<script>
import DatetimeFilter from "../mixins/DatetimeFilter.js";
import CardStatus from "./card-status.vue";

export default {
    props: ['card'],
    mixins: [DatetimeFilter],
    components: {CardStatus},
    computed: {
        request() {
            return this.card.request || {}
        },
        customer() {
            const request = this.request
            return (request.customer_id)
                ? {
                    id: request.customer_id,
                    name: request.customer ? request.customer.full_name : request.customer_id,
                    url: this.getRoute('customers.detail', {id: request.customer_id})
                }
                : null
        },
        recipient() {
            return (this.card.recipient_id)
                ? {
                    id: this.card.recipient_id,
                    name: this.card.recipient ? this.card.recipient.full_name : this.card.recipient_id,
                    url: this.getRoute('customers.detail', {id: this.card.recipient_id})
                }
                : null
        }
    }
}
</script>

