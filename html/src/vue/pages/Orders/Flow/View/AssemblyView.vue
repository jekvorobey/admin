<template>
    <layout-main back hide-title>
        <div class="row align-items-stretch justify-content-start order-header mt-3">
            <div class="col-md-4">
                <div class="shadow p-3 height-100">
                    <div class="row">
                        <div class="col-sm-12">
                            <h2>Заказ {{ order.number }}</h2>
                            <div style="height: 40px">
                                <order-status :status='order.status'/>
                                <span class="badge badge-danger" v-if="isCancel">Отменен</span>

                                <b-dropdown text="Действия" class="float-right" size="sm" v-if="(isNotPaid || this.order.status.id < 9) && !isCancel">
                                    <b-dropdown-item-button v-if="isNotPaid && !isCancel" @click="payOrder()">
                                        Оплатить
                                    </b-dropdown-item-button>
                                    <b-dropdown-item-button v-if="isPreOrderStatus || isCreatedStatus" @click="changeOrderStatus(4)">
                                        Ожидает подтверждения Мерчантом
                                    </b-dropdown-item-button>
                                    <b-dropdown-item-button v-if="this.order.status.id < 9 && !isCancel" @click="cancelOrder()">
                                        Отменить заказ
                                    </b-dropdown-item-button>
                                </b-dropdown>
                            </div>

                            <p class="text-secondary mt-3">
                                Последнее изменение:<span class="float-right">{{ order.updated_at }}</span>
                            </p>
                            <p class="text-secondary mt-3">
                                Дата заказа:<span class="float-right">{{ order.created_at }}</span>
                            </p>

                            <p class="text-secondary" v-if="order.customer">
                                Покупатель:<span class="float-right">{{ order.customer.last_name }} {{ order.customer.first_name }} {{ order.customer.middle_name }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="shadow p-3 height-100">
                    <h2>
                        Сумма заказа {{preparePrice(order.price)}} руб.
                        <fa-icon icon="question-circle" v-b-popover.hover="tooltipOrderCost"></fa-icon>
                    </h2>
                    <div style="height: 40px">
                        <payment-status :status='order.payment_status'/>
                    </div>

                    <p class="text-secondary" v-if="order.discount">
                        Скидка:  <span class="float-right text-danger measure">руб. </span>
                        <span class="float-right text-danger">-{{order.discount}}</span>
                    </p>
                    <p class="text-secondary mt-3">
                        Сумма без скидки: <span class="float-right text-danger measure">руб.</span>
                        <span class="float-right text-danger">{{preparePrice(order.cost)}}</span>
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="shadow p-3 height-100">
                    <h2>{{order.total_qty}} ед. товара</h2>
                    <p class="text-secondary">
                        Вес:<span class="float-right">{{ order.weight }} г</span>
                    </p>
                </div>
            </div>
        </div>

        <v-tabs :current="nav.currentTab" :items="nav.tabs" @nav="tab => nav.currentTab = tab"></v-tabs>
        <basket-items-tab
                v-if="nav.currentTab === 'products'"
                :basket-items="order.basket.items"
        ></basket-items-tab>
        <history-tab
                v-if="nav.currentTab === 'history'"
                :history="order.history"
        ></history-tab>
        <customer-history-tab
                v-if="nav.currentTab === 'customer_history'"
                :history="order.customer_history"
                :roundFloat="roundFloat"
        ></customer-history-tab>
        <main-tab
                v-if="nav.currentTab === 'main'"
                :order="order"
        ></main-tab>
        <delivery-tab
                v-if="nav.currentTab === 'delivery'"
                :deliveries="order.deliveries"
                :statuses="order.delivery_statuses"
                :services="order.delivery_services"
        ></delivery-tab>
        <shipment-tab
                v-if="nav.currentTab === 'shipment'"
                :shipments="order.shipments"
                :roundFloat="roundFloat"
        ></shipment-tab>
    </layout-main>
</template>

<script>

    import Services from '../../../../../scripts/services/services';
    import VTabs from '../../../../components/tabs/tabs.vue';
    import BasketItemsTab from './components/basket-items-tab.vue';
    import HistoryTab from './components/history-tab.vue';
    import CustomerHistoryTab from './components/customer-history-tab.vue';
    import MainTab from './components/main-tab.vue';
    import DeliveryTab from './components/delivery-tab.vue';
    import ShipmentTab from './components/shipment-tab.vue';

    export default {
    components: {
        VTabs,

        BasketItemsTab,
        HistoryTab,
        CustomerHistoryTab,
        MainTab,
        DeliveryTab,
        ShipmentTab,
    },
    props: {
        iOrder: {},
    },
    data() {
        return {
            order: this.iOrder,
            history: this.iOrder.history,
            customer_history: this.iOrder.customer_history,

            nav: {
                currentTab: 'main',
                tabs: [
                    {value: 'main', text: 'Общее'},
                    {value: 'delivery', text: 'Доставки'},
                    {value: 'shipment', text: 'Отправления'},
                    {value: 'history', text: 'История'},
                    // {value: 'transactions', text: 'Транзакции'},
                    {value: 'customer_history', text: 'История заказов клиента'},
                ]
            }
        };
    },

    methods: {
        paymentMethodClass(paymentMethodId) {
            switch (paymentMethodId) {
                case 1: return 'badge-success';
                case 2: return 'badge-warning';
                case 3: return 'badge-danger';
                default: return 'badge-secondary';
            }
        },
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
        /**
         * Округляет дробные числа до 2 знака после запятой
         * @param value
         * @returns {number}
         */
        roundFloat(value) {
            return Math.floor(value * 100) / 100;
        },
    },
    computed: {
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
    },
    mounted() {
    },
};
</script>
<style scoped>
    .order-header {
        min-height: 200px;
    }
    .order-header > div {
        padding: 16px 0 16px 16px;
    }
    .order-header img {
        max-height: calc( 200px - 16px * 2 );
    }
    .order-header p {
        margin: 0;
        padding: 0;
    }
    .height-100 {
        min-height: 100%;
        height: 100%;
    }
    .measure {
        width: 30px;
        margin-left: 10px;
    }
</style>
