<template>
    <b-card>
        <b-row>
            <b-col>
                <p class="font-weight-bold">Инфопанель</p>
            </b-col>
            <b-col>
                <b-dropdown text="Действия" class="float-right" size="sm" v-if="(isNotPaid || this.order.status.id < 9) && !isCancel">
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
                    <b-dropdown-item-button>
                        Добавить служебный комментарий
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="isPreOrderStatus || isCreatedStatus" @click="changeOrderStatus(4)">
                        Ожидает подтверждения Мерчантом
                    </b-dropdown-item-button>
                    <b-dropdown-item-button v-if="this.order.status.id < 9 && !isCancel" @click="cancelOrder()">
                        Отменить заказ
                    </b-dropdown-item-button>
                </b-dropdown>
            </b-col>
        </b-row>

        <b-row class="mb-3">
            <b-col><span class="font-weight-bold">Заказ {{ order.number }} от {{order.created_at}}</span></b-col>
            <b-col>
                <span class="font-weight-bold">Статус заказа:</span>
                <order-status :status='order.status'/>
                <span class="badge badge-danger" v-if="isCancel">Отменен</span>
                <span class="badge badge-danger" v-if="isProblem">Проблемный</span>
            </b-col>
        </b-row>
        <b-row class="mb-3">
            <b-col>
                <span class="font-weight-bold">Покупатель:</span> <a :href="getRoute('customers.detail', {id: order.customer_id})" target="_blank" v-if="order.customer && order.customer.user">{{ order.customer.user.full_name }}</a><span v-else>N/A</span>
            </b-col>
            <b-col>
                <span class="font-weight-bold">Сегмент:</span> N/A
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <span class="font-weight-bold">Тип доставки:</span> {{ order.delivery_type.name }}
            </b-col>
            <b-col>
                <span class="font-weight-bold">Город доставки:</span> {{ order.delivery_cities }}
            </b-col>
        </b-row>
        <b-row class="mb-3">
            <b-col>
                <span class="font-weight-bold">Способ доставки:</span>
                <template v-for="(delivery_method, key) in order.delivery_methods">
                    <span v-if="key > 0">, </span>{{ delivery_method.name }}
                </template>
            </b-col>
            <b-col>
                <span class="font-weight-bold">Логистический оператор:</span>
                <template v-for="(delivery_service, key) in order.delivery_services">
                    <span v-if="key > 0">, </span><a :href="getRoute('deliveryService.detail', {id: delivery_service.id})" target="_blank">{{ delivery_service.name }}</a>
                </template>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <span class="font-weight-bold">Сумма заказа:</span> {{preparePrice(order.price)}} руб.
                <fa-icon icon="question-circle" v-b-popover.hover="tooltipOrderCost"></fa-icon>
            </b-col>
            <b-col>
                <span class="font-weight-bold">Статус оплаты:</span>
                <payment-status :status='order.payment_status'/>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <span class="font-weight-bold">Способ оплаты:</span> {{order.payment_methods}}
            </b-col>
            <b-col>
                <span class="font-weight-bold">К оплате:</span> {{preparePrice(order.to_pay)}} руб.
            </b-col>
        </b-row>
        <b-row class="mb-3">
            <b-col>
                <span class="font-weight-bold">Стоимость товаров:</span> {{preparePrice(order.product_price)}} руб.
            </b-col>
            <b-col>
                <span class="font-weight-bold">Стоимость доставки:</span> {{preparePrice(order.delivery_price)}} руб.
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <span class="font-weight-bold">Кол-во ед. товара:</span> {{order.total_qty}} шт.
            </b-col>
            <b-col>
                <span class="font-weight-bold">Вес заказа:</span> {{order.weight}} г.
            </b-col>
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
                    this.order = data.order;
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
                    this.order = data.order;
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
            return this.order.status.id === statusId;
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
            return this.isStatus(0);
        },
        isCreatedStatus() {
            return this.isStatus(1);
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
    },
};
</script>
