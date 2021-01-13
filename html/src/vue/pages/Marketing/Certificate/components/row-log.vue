<template>
    <tr>
        <td>{{ item.transaction_id }}</td>
        <td>
            <a v-if="order" :href="getRoute('orders.detail', {id: order.id})">{{ order.number }}</a>
            <span v-else>-</span>
        </td>
        <td>{{ item.certificate_id }}</td>
        <td>{{ typeText }}</td>
        <td :style="balanceDiff.style">{{ balanceDiff.amount }}</td>
        <td><card-status :status="item.prev_status"/></td>
        <td><card-status :status="item.new_status"/></td>

        <td>
            <a v-if="recipient" :href="recipient.link">{{ recipient.name }}</a>
            <span v-else>-</span>
        </td>
        <td>
            <a v-if="creator" :href="creator.link">{{ creator.name }}</a>
            <span v-else>-</span>
        </td>
        <td>{{ item.created_at | datetime }}</td>
    </tr>
</template>

<script>
import CardStatus from "./card-status.vue";
import DatetimeFilter from "../mixins/DatetimeFilter.js";

export default {
    props: ['item'],
    components: {CardStatus},
    mixins: [DatetimeFilter],
    data() {
        return {
            showModal: false
        }
    },
    computed: {
        transaction() {
            return this.item.transaction || {}
        },
        certificate() {
            return this.item.certificate || {}
        },
        order() {
            const tr = this.transaction
            return (tr.order_id)
                ? {'id': tr.order_id, 'number': tr.order_number || tr.order_id}
                : null
        },
        typeText() {
            switch (this.transaction.type) {
                case 1: return 'Активация';
                case 2: return 'Ручное изменение';
                case 3: return 'Оплата покупки';
                case 4: return 'Возврат средств';
                case 5: return 'Изменение статуса';
                default: return 'N/A';
            }
        },
        balanceDiff() {
            if (this.item.amount > 0) {
                return {'style': 'color: green', 'amount': '+' + this.item.amount}
            } else if (this.item.amount < 0) {
                return {'style': 'color: red', 'amount': '' + this.item.amount}
            } else {
                return {'style': '', 'amount': '-'}
            }
        },
        creator() {
            const user = this.transaction.user
            return (!this.transaction.user_id)
                ? null
                : {
                    'link': this.getRoute('settings.userDetail', {id: this.transaction.user_id}),
                    'name': user ? user.short_name : this.transaction.user_id
                }
        },
        recipient() {
            const recipient = this.certificate.recipient
            return (!this.certificate.recipient_id)
                ? null
                : {
                    'link': this.getRoute('customers.detail', {id: this.certificate.recipient_id}),
                    'name': recipient ? recipient.short_name : this.certificate.recipient_id
                }
        },
    },
    methods: {
        showDetails() {
            this.showModal = true
        },
        closeDetails() {
            this.showModal = false
        }
    }
}
</script>
