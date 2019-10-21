<template>
    <layout-main back>
        <div class="row align-items-stretch justify-content-start order-header">
            <div class="col-md-4">
                <div class="shadow p-3 height-100">
                    <h2>Заказ {{ order.number }} от {{ order.created_at }}</h2>
                    <div style="height: 40px">
                        <span class="badge" :class="statusClass(order)">
                            {{ order.status.name || 'N/A' }}
                        </span>
                        <button class="btn btn-primary">Принять</button>
                        <button class="btn btn-secondary">Отклонить</button>
                    </div>

                    <p class="text-secondary mt-3">
                        Последнее изменение:<span class="float-right">{{ order.updated_at }}</span>
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="shadow p-3 height-100">
                <h2>
                    Сумма заказа {{preparePrice(order.cost)}} руб.
                    <fa-icon icon="question-circle" v-b-popover.hover="tooltipOrderCost"></fa-icon>
                </h2>
                <div style="height: 40px">
                    <span class="badge" :class="paymentMethodClass(order.payment_method.id)">
                        {{ order.payment_method.name || 'N/A' }}
                    </span>
                    <span class="float-right text-secondary"> оплачено: {{order.paid || 0}} руб.</span>
                </div>
                <p class="text-secondary" v-if="order.discount">
                    Скидка:  <span class="float-right text-danger measure">руб. </span>
                    <span class="float-right text-danger">-{{order.discount}}</span>
                </p>
                <p class="text-secondary">
                    {{order.delivery_method.name}}:
                    <span class="float-right text-danger measure">руб.</span>
                    <span class="float-right text-danger">+{{order.delivery_cost || 0}}</span>
                </p>
                <p class="text-secondary mt-3">
                    Сумма без скидки: <span class="float-right text-danger measure">руб.</span>
                    <span class="float-right text-danger">{{preparePrice(order.cost_without_discount)}}</span>
                </p>
                <p class="text-secondary">
                    Сумма без скидки и доставки: <span class="float-right text-danger measure">руб.</span>
                    <span class="float-right text-danger">{{preparePrice(order.products_cost)}}</span>
                </p>
            </div>
            </div>
            <div class="col-md-4">
                <div class="shadow p-3 height-100">
                <h2>{{order.totalQty}} ед.</h2>
                <div style="height: 40px">
                    <span class="badge" :class="deliveryMethodClass(order.delivery_method.id)">
                        {{ order.delivery_method.name || 'N/A' }}
                    </span>
                    <span class="float-right text-secondary"> кол-во коробок: {{order.box_qty || 1}} шт.</span>
                </div>
                <p class="text-secondary">
                    Вес:<span class="float-right">{{ order.weight }} г</span>
                </p>
                <p class="text-secondary">
                    Тип упаковки:<span class="float-right">{{ order.packaging_type }}</span>
                </p>
                <p class="text-secondary mt-3">
                    Склад отгрузки:
                    <span class="float-right">{{order.delivery_store.name}}</span>
                </p>
                <p class="text-secondary">
                    {{order.delivery_method.name}} по адресу:
                    <span class="float-right">{{order.delivery_address}}</span>
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
    </layout-main>
</template>

<script>

import {mapGetters} from "vuex";
import VTabs from '../../../../components/tabs/tabs.vue';

import BasketItemsTab from './components/basket-items-tab.vue';
import HistoryTab from './components/history-tab.vue';

export default {
    components: {
        VTabs,

        BasketItemsTab,
        HistoryTab,
    },
    props: {
        iOrder: {},
    },
    data() {
        return {
            order: this.iOrder,
            history: this.iOrder.history,

            nav: {
                currentTab: 'products',
                tabs: [
                    {value: 'main', text: 'Общее'},
                    {value: 'delivery', text: 'Доставка'},
                    {value: 'products', text: 'Состав заказа'},
                    {value: 'history', text: 'История'},
                    {value: 'transactions', text: 'Транзакции'},
                    {value: 'customer_history', text: 'История заказов клиента'},
                ]
            }
        };
    },

    methods: {
        statusClass(orderId) {
            switch (orderId) {
                case 1: return 'badge-danger';
                case 2: return 'badge-warning';
                case 3: return 'badge-success';
                default: return 'badge-secondary';
            }
        },
        paymentMethodClass(paymentMethodId) {
            switch (paymentMethodId) {
                case 1: return 'badge-success';
                case 2: return 'badge-warning';
                case 3: return 'badge-danger';
                default: return 'badge-secondary';
            }
        },
        deliveryMethodClass(deliveryMethodId) {
            switch (deliveryMethodId) {
                case 1: return 'badge-success';
                case 2: return 'badge-warning';
                default: return 'badge-secondary';
            }
        },
    },
    computed: {
        ...mapGetters(['getRoute']),
        tooltipOrderCost() {
            return 'С учётом всех скидок и доставки';
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
