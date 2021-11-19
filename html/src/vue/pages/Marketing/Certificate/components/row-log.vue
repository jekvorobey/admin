<template>
    <tr>
        <td>{{ item.transaction_id }}</td>
        <td v-if="canUpdate(blocks.orders)">
            <a v-if="order" :href="getRoute('orders.detail', {id: order.id})">{{ order.number }}</a>
            <span v-else>-</span>
        </td>
        <td v-else>
            {{ order.number }}
        </td>
        <td>{{ item.certificate_id }}</td>
        <td>
            <a href="#" v-if="changes" @click.prevent="showDetails">{{ typeText }}</a>
            <span v-else>{{ typeText }}</span>
        </td>

        <td :style="balanceDiff.style">{{ balanceDiff.amount }}</td>
        <td><card-status :status="item.prev_status"/></td>
        <td><card-status :status="item.new_status"/></td>

        <td v-if="canUpdate(blocks.clients)">
            <a v-if="recipient" :href="recipient.link">{{ recipient.name }}</a>
            <span v-else>-</span>
        </td>
        <td v-else>
            <span v-if="recipient">{{ recipient.name }}</span>
            <span v-else>-</span>
        </td>
        <td v-if="canUpdate(blocks.settings)">
            <a v-if="creator" :href="creator.link">{{ creator.name }}</a>
            <span v-else>-</span>
        </td>
        <td v-else>
            <span v-if="creator">{{ creator.name }}</span>
            <span v-else>-</span>
        </td>
        <td>{{ item.created_at | datetime }}
            <ModalWindow v-if="showModal" type="wide" :close="closeDetails">
                <div slot="header">Лог запись № {{ item.id}}</div>
                <div slot="body">
                    <table style="width: 100%">
                        <tr>
                            <th style="width: 30%">Ключ</th>
                            <th style="width: 35%">Старое значение</th>
                            <th style="width: 35%">Новое значение</th>
                        </tr>
                        <tr v-for="(change, key) in changes" :key="key">
                            <td>{{ key }}</td>
                            <td>{{ change.old }}</td>
                            <td>{{ change.new }}</td>
                        </tr>
                    </table>
                </div>

            </ModalWindow>
        </td>
    </tr>
</template>

<script>
import CardStatus from "./card-status.vue";
import DatetimeFilter from "../mixins/DatetimeFilter.js";
import ModalWindow from '../../../../components/controls/modal/modal.vue'

export default {
    props: ['item'],
    components: {CardStatus, ModalWindow},
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
        changes() {
            return this.item.changes;
        },
        typeText() {
            switch (this.transaction.type) {
                case 1: return 'Активация';
                case 2: return 'Ручное изменение';
                case 3: return 'Оплата покупки';
                case 4: return 'Возврат средств';
                case 5: return 'Изменение статуса';
                case 6: return 'Изменение текстов';
                case 7: return 'Продление активации';
                case 8: return 'Отправка уведомления';
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
