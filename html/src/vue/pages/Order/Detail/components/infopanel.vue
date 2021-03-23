<template>
    <b-card>
        <b-row>
            <b-col>
                <p class="font-weight-bold">Инфопанель</p>
            </b-col>
            <b-col>
                <button @click="makeDial" class="btn btn-info btn-sm float-right">Позвонить</button>
                <b-dropdown text="Действия" class="float-right" size="sm" v-if="(isNotPaid || (this.order.status &&
                this.order.status.id < orderStatuses.done.id)) && !isCancel">
                    <b-dropdown-item-button>
                        Пометить, как проблемный
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="isNotPaid && !isCancel" @click="payOrder()">
                        Оплатить
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="!isNotPaid">
                        Вернуть деньги
                    </b-dropdown-item-button>
                    <b-dropdown-item-button>
                        Отправить уведомление клиенту
                    </b-dropdown-item-button>
                    <b-dropdown-item-button>
                        Отправить уведомление мерчанту
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="isPreOrderStatus || isCreatedStatus" @click="changeOrderStatus(orderStatuses.awaitingConfirmation.id)">
                        Ожидает подтверждения Мерчантом
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="this.order.status && this.order.status.id < 9 && !isCancel"
                            @click="cancelOrder()">
                        Отменить заказ
                    </b-dropdown-item-button>
                </b-dropdown>
            </b-col>
        </b-row>

        <b-row class="mb-3">
            <div class="col-sm-6">
                <span class="font-weight-bold">Заказ {{ order.number }} от {{order.created_at}}</span><br>
                <order-type :type='order.type'/>
            </div>
            <div class="col-sm-6">
                <span class="font-weight-bold">Статус заказа:</span>
                <span>
                    <order-status :status='order.status'/>
                    {{order.status_at}}
                </span>
                <p v-if="order.is_require_check">
                    <span class="badge badge-danger">Требует проверки АОЗ</span>
                </p>
                <p v-if="isProblem">
                    <span class="badge badge-danger">Проблемный</span>
                    {{order.is_problem_at}}
                </p>
                <p v-if="isCancel">
                    <span class="badge badge-danger">Отменен</span>
                    {{order.is_canceled_at}}
                </p>
            </div>
        </b-row>
        <b-row>
            <div class="col-sm-6">
                <span class="font-weight-bold">Покупатель:</span> <a :href="getRoute('customers.detail', {id: order.customer_id})" target="_blank" v-if="order.customer && order.customer.user">{{ order.customer.user.full_name ? order.customer.user.full_name : order.customer.user.login}}</a><span v-else>N/A</span>
            </div>
            <div class="col-sm-6">
                <span class="font-weight-bold">Сегмент:</span> N/A
            </div>
        </b-row>
        <b-row class="mb-3">
            <div class="col-sm-6">
                <span class="font-weight-bold">Телефон:</span> <a :href="customerPhoneLink" target="_blank" v-if="order.customer && order.customer.user">{{ order.customer.user.phone}}</a><span v-else>N/A</span>
            </div>
        </b-row>
        <b-row>
            <div class="col-sm-6">
                <span class="font-weight-bold">Тип доставки:</span>
                    <span v-if="order.deliveries.length > 1">Несколькими доставками</span>
                    <span v-else>Одной доставкой</span>
            </div>
            <div class="col-sm-6">
                <span class="font-weight-bold">Город доставки:</span> {{ order.delivery_cities }}
            </div>
        </b-row>
        <b-row class="mb-3">
            <div class="col-sm-6">
                <span class="font-weight-bold">Способ доставки:</span>
                <template v-for="(delivery_method, key) in order.delivery_methods">
                    <span v-if="key > 0">, </span>{{ delivery_method.name }}
                </template>
            </div>
            <div class="col-sm-6">
                <span class="font-weight-bold">Логистический оператор:</span>
                <template v-for="(delivery_service, key) in order.delivery_services">
                    <span v-if="key > 0">, </span><a :href="getRoute('deliveryService.detail', {id: delivery_service.id})" target="_blank">{{ delivery_service.name }}</a>
                </template>
            </div>
        </b-row>
        <b-row>
            <div class="col-sm-6">
                <span class="font-weight-bold">Сумма заказа:</span> {{preparePrice(order.price)}} руб.
                <fa-icon icon="question-circle" v-b-popover.hover="tooltipOrderCost"></fa-icon>
            </div>
            <div class="col-sm-6">
                <span class="font-weight-bold">Статус оплаты:</span>
                <payment-status :status='order.payment_status'/>
                {{order.payment_status_at}}
            </div>
        </b-row>
        <b-row class="mb-3">
            <div class="col-sm-6">
                <span class="font-weight-bold">Способ оплаты:</span> {{order.payment_methods}}
            </div>
            <div class="col-sm-6">
                <span class="font-weight-bold">К оплате:</span> {{preparePrice(order.to_pay)}} руб.
            </div>
        </b-row>
        <b-row class="mb-3">
            <b-col>
                <span class="font-weight-bold">Тип подтверждения :</span> {{order.confirmation_type.name}}
            </b-col>
        </b-row>
        <b-row>
            <div class="col-sm-6">
                <span class="font-weight-bold">Кол-во ед. товара:</span> {{order.total_qty}} шт.
            </div>
            <div class="col-sm-6">
                <span class="font-weight-bold">Вес заказа:</span> {{order.weight}} г.
            </div>
        </b-row>
    </b-card>
</template>

<script>
    import Services from '../../../../../scripts/services/services.js';

    import {validationMixin} from 'vuelidate';

    export default {
    name: 'infopanel',
    components: {},
    mixins: [
        validationMixin,
    ],
    props: [
        'model',
    ],
    methods: {
        changeOrderStatus(statusId) {
            let errorMessage = 'Ошибка при изменении статуса заказа';

            Services.showLoader();
            Services.net().put(this.getRoute('orders.changeStatus', {id: this.order.id}), null,
                {'status': statusId}).then(data => {
                if (data.status) {
                    this.order.status = data.status;
                    Services.msg("Изменения сохранены");
                } else {
                    Services.msg(errorMessage, 'danger');
                }
            }, () => {
                Services.msg(errorMessage, 'danger');
            }).finally(data => {
                Services.hideLoader();
            });
        },
        payOrder() {
            let errorMessage = 'Ошибка при оплате заказа';

            Services.showLoader();
            Services.net().put(this.getRoute('orders.pay', {id: this.order.id})).then(data => {
                if (data.order) {
                    this.$set(this, 'order', data.order);
                    this.$set(this.order, 'shipments', data.order.shipments);
                    Services.msg("Изменения сохранены");
                } else {
                    Services.msg(errorMessage, 'danger');
                }
            }, () => {
                Services.msg(errorMessage, 'danger');
            }).finally(data => {
                Services.hideLoader();
            });
        },
        cancelOrder() {
            let errorMessage = 'Ошибка при отмене заказа';

            Services.showLoader();
            Services.net().put(this.getRoute('orders.cancel', {id: this.order.id})).then(data => {
                if (data.order) {
                    this.$set(this, 'order', data.order);
                    this.$set(this.order, 'shipments', data.order.shipments);
                    Services.msg("Изменения сохранены");
                } else {
                    Services.msg(errorMessage, 'danger');
                }
            }, () => {
                Services.msg(errorMessage, 'danger');
            }).finally(data => {
                Services.hideLoader();
            });
        },
        isStatus(statusId) {
            return this.order.status && this.order.status.id === statusId;
        },
        makeDial() {
            Services.showLoader();
            Services.net().post(this.getRoute('customers.detail.dial', {id: this.order.customer.id}), null, {provider: 'check_order'})
                .then(data => {
                    Services.msg("Запрос на звонок отправлен");
                }).finally(data => {
                    Services.hideLoader();
                })
        },
    },
    computed: {
        order: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        tooltipOrderCost() {
            return 'С учётом всех скидок и доставки';
        },
        isPreOrderStatus() {
            return this.isStatus(this.orderStatuses.preOrder.id);
        },
        isCreatedStatus() {
            return this.isStatus(this.orderStatuses.created.id);
        },
        isNotPaid() {
            return this.order.payment_status.id !== 2;
        },
        isCancel() {
            return this.order.is_canceled;
        },
        isProblem() {
            return this.order.is_problem;
        },
        customerPhoneLink() {
            return 'tel:' + (this.order.customer && this.order.customer.user ? this.order.customer.user.phone : '');
        }
    },
};
</script>
