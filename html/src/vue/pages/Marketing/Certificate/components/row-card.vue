<template>
    <tr>
        <td><a :href="getRoute('orders.detail', {id: request.order_id})">{{ request.order_number }}</a></td>
        <td>{{ card.id }}</td>
        <td>{{ card.pin }}</td>
        <td>{{ card.price.toLocaleString() }}</td>
        <td>{{ card.balance.toLocaleString() }}</td>
        <td>{{ card.created_at | datetime }}</td>
        <td>{{ card.notified_at | datetime }}</td>
        <td>{{ card.activated_at | datetime }}</td>
        <td>{{ card.expire_at | datetime }}</td>
        <td><card-status :status="card.status"/></td>
        <td><a v-if="customer" :href="customer.url">{{ customer.name }}</a></td>
        <td><a v-if="recipient" :href="recipient.url">{{ recipient.name }}</a></td>
        <td>
            <a :href="getRoute('orders.detail', {id: orderPayTransaction.order_id})" :key="orderPayTransaction.id"
               v-for="orderPayTransaction in orderPayTransactions">
                {{ orderPayTransaction.order_number }}
            </a>
        </td>
        <td>{{request.comment}}</td>
        <td>{{request.to_email}}</td>
        <td>{{request.to_phone}}</td>
        <td>
            <b-button class="btn btn-info btn-sm m-1" @click="showModalInputDay" :id="btn_id()" v-if="card.status == 306 || card.status == 301">
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

          <div class="custom-control custom-switch m-1">
            <input @click="changeStatus"
                   type="checkbox"
                   class="custom-control-input"
                   :id="'status-switcher-' + card.id"
                   :checked="card.activated_at"
                   :disabled="card.status >= 305 || (!card.pin && card.status <= 301)"
            />
            <label class="custom-control-label" :for="'status-switcher-' + card.id"></label>
          </div>

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
          // console.log(this.card.request);
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

        changeStatus(e){
          const newStatus = e.target.checked;
          const id = this.card.id
          let req = {};

          e.preventDefault();
          Services.showLoader();

          if (!this.card.activated_at) {
            req = Services.net().post(this.getRoute('certificate.card_activate'), {
              pin: this.card.pin,
              customer_id: this.card.request.customer_id
            })
          } else {
            req = Services.net().put(this.getRoute('certificate.card_deactivate', {id}))
          }

          req.then(() => {
            e.target.checked = newStatus;
            this.$emit('update')
          })
          .catch(() => {
            e.target.checked = !newStatus;
          })
          .finally(() => Services.hideLoader())
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

