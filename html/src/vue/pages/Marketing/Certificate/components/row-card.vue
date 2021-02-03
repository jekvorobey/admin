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
        <td>{{request.comment}}</td>
        <td>{{request.to_email}}</td>
        <td>{{request.to_phone}}</td>
        <td>
            <a :href="redoLink" class="btn btn-info btn-sm" style="height: 31px; padding-top: 7px;">
                <fa-icon icon="redo-alt" class="float-right media-btn" v-b-popover.hover="'Продлить срок активации ПС'"></fa-icon>
            </a>
            <a :href="sendLink" class="btn btn-info btn-sm" style="height: 31px; padding-top: 7px;">
                <fa-icon icon="paper-plane" class="float-right media-btn" v-b-popover.hover="'Отправить сертификат Получателю'"></fa-icon>
            </a>
            <a :href="editLink" class="btn btn-info btn-sm" style="height: 31px; padding-top: 7px;">
                <fa-icon icon="pencil-alt" class="float-right media-btn" v-b-popover.hover="'Редактировать'"></fa-icon>
            </a>
        </td>
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
        },
        redoLink() {
            return 'javascript:void(0)'
        },
        sendLink() {
            return 'javascript:void(0)'
        },
        editLink() {
            return this.getRoute('certificate.card_edit', {id: this.card.id})
        }
    }
}
</script>

