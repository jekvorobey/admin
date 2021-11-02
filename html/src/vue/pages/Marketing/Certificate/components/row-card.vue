<template>
    <tr>
        <td v-if="canView(blocks.orders)"><a :href="getRoute('orders.detail', {id: request.order_id})">{{ request.order_number }}</a></td>
        <td v-else>{{ request.order_number }}</td>
        <td>{{ card.id }}</td>
        <td>{{ card.pin }}</td>
        <td>{{ card.price.toLocaleString() }}</td>
        <td>{{ card.balance.toLocaleString() }}</td>
        <td>{{ card.created_at | datetime }}</td>
        <td>{{ card.notified_at | datetime }}</td>
        <td>{{ card.activated_at | datetime }}</td>
        <td>{{ card.expire_at | datetime }}</td>
        <td><card-status :status="card.status"/></td>
        <td v-if="canView(blocks.clients)"><a v-if="customer" :href="customer.url">{{ customer.name }}</a></td>
        <td v-else>{{ customer.name }}</td>
        <td v-if="canView(blocks.clients)"><a v-if="recipient" :href="recipient.url">{{ recipient.name }}</a></td>
        <td v-else>{{ recipient.name }}</td>
        <td v-if="canView(blocks.orders)">
            <a :href="getRoute('orders.detail', {id: orderPayTransaction.order_id})" :key="orderPayTransaction.id"
               v-for="orderPayTransaction in orderPayTransactions">
                {{ orderPayTransaction.order_number }}
            </a>
        </td>
        <td v-else>
            <span v-for="orderPayTransaction in orderPayTransactions">
              {{ orderPayTransaction.order_number }}
            </span>
        </td>
        <td>{{request.comment}}</td>
        <td>{{request.to_email}}</td>
        <td>{{request.to_phone}}</td>
        <td v-if="canUpdate(blocks.marketing)">
            <b-button class="btn btn-info btn-sm" style="height: 31px; padding-top: 7px;" @click="showModalInputDay" :id="btn_id()" v-if="card.status == 306 || card.status == 301">
                <fa-icon icon="redo-alt" class="float-right media-btn" v-b-popover.hover="'Продлить срок активации'"></fa-icon>
            </b-button>
            <b-popover :show.sync="popoverShow" placement="auto" ref="popover" :target="btn_id()">
                <template slot="title">
                    <v-input v-model="activatePeriod">Добавить дней</v-input>
                </template>
                <div>
                    <button @click="onClose" class="btn btn-sm btn-secondary">Отмена</button>
                    <button @click="updateActivationPeriod" class="btn btn-sm btn-success">Продлить</button>
                </div>
            </b-popover>

            <b-button
                class="btn btn-info btn-sm m-1"
                @click="togglePopover"
                :id="`popover-id-${card.id}`"
                :disabled="!([302, 303, 304].includes(card.status))"
            >
                <fa-icon icon="redo-alt"
                         class="float-right media-btn"
                         v-b-popover.hover="'Деактивировать сертификат'">
                </fa-icon>
            </b-button>

            <b-popover placement="auto" ref="popover" :target="`popover-id-${card.id}`">
                <template slot="title">Деактивировать сертификат</template>
                <div>
                    <button @click="togglePopover" class="btn btn-sm btn-secondary">Отмена</button>
                    <button @click="deactivateCertificate" class="btn btn-sm btn-success">Деактивировать</button>
                </div>
            </b-popover>

            <a v-if="!!card.pin" :href="sendLink" class="btn btn-info btn-sm m-1" :id="'btn-notify-' + card.id">
                <fa-icon icon="paper-plane" class="float-right media-btn" v-b-popover.hover="'Отправить сертификат Получателю'"></fa-icon>
            </a>
            <b-popover :show.sync="notificationPopoverShow" placement="auto" ref="popover" :target="'btn-notify-' + card.id">
                <template slot="title">Отправить уведомление</template>
                <div>
                    <button @click="notificationPopoverShow = false" class="btn btn-sm btn-secondary">Отмена</button>
                    <button @click="sendNotification" class="btn btn-sm btn-success">Отправить</button>
                </div>
            </b-popover>

            <a :href="editLink" class="btn btn-info btn-sm m-1">
                <fa-icon icon="pencil-alt" class="float-right media-btn" v-b-popover.hover="'Редактировать'"></fa-icon>
            </a>
        </td>
    </tr>
</template>

<script>
import DatetimeFilter from "../mixins/DatetimeFilter.js";
import CardStatus from "./card-status.vue";
import Services from "../../../../../scripts/services/services";
import VInput from '../../../../components/controls/VInput/VInput.vue';

export default {
    props: ['card'],
    mixins: [DatetimeFilter],
    components: {CardStatus, VInput},
    computed: {
        request() {
            return this.card.request || {}
        },
        orderPayTransactions() {
            return this.card.orderPayTransactions || {}
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
        sendLink() {
            return 'javascript:void(0)'
        },
        editLink() {
            return this.getRoute('certificate.card_edit', {id: this.request.id})
        }
    },
    data() {
        return {
            activatePeriod: null,
            popoverShow: false,
            notificationPopoverShow: false,
            input_count: 0
        };
    },
    methods: {
        updateActivationPeriod() {
            this.onClose()
            Services.showLoader();
            let form = {
                days: this.activatePeriod
            }
            const id = this.card.id
            Services.net().post(this.getRoute('certificate.update_activation_period', {id}), {}, form)
                .then(() => {
                    Services.hideLoader();
                    Services.msg("Изменения сохранены")
                    this.$emit('update')
                })
        },

        togglePopover() {
          this.$root.$emit('bv::hide::popover')
          this.$root.$emit('bv::show::popover', `popover-id-${this.card.id}`)
        },

        deactivateCertificate(e) {
            const newStatus = e.target.checked;
            const id = this.card.id

            e.preventDefault();
            Services.showLoader();

            Services.net().put(this.getRoute('certificate.card_deactivate', {id})).then(() => {
                e.target.checked = newStatus;
                this.$emit('update')
            })
            .catch(() => {
                e.target.checked = !newStatus;
            })
            .finally(() => {
                Services.hideLoader();
                this.$root.$emit('bv::hide::popover')
            })
        },

        sendNotification() {
            this.notificationPopoverShow = false
            Services.showLoader();
            const id = this.card.id
            Services.net().post(this.getRoute('certificate.send_notification', {id}), {})
                .then(() => {
                    Services.hideLoader();
                    Services.msg("Уведомление отправлено")
                    this.$emit('update')
                })
        },
        showModalInputDay() {
            this.popoverShow = !this.popoverShow;
        },
        btn_id() {
            if (this.input_id === undefined) {
                this.input_id = this.newBtnId();
            }
            return this.input_id;
        },
        newBtnId() {
            return `btn-expired-${this.card.id}`;
        },
        onClose() {
            this.popoverShow = false
        },
    }
}
</script>

