<template>
    <layout-main back>
        <div class="align-items-stretch justify-content-start order-header mt-3">
            <div class="shadow p-3 height-100">
                <div class="row">
                    <div class="col-sm-12">
                        <h4>Заказ {{ order.number }}
                            <span class="badge ml-2" :class="statusClass(order.status.id)">
                                {{ order.status.name || 'N/A' }}
                            </span>
                        </h4>

                        <p class="text-secondary mt-3">
                            Последнее изменение:<span class="float-right">{{ order.updated_at }}</span>
                        </p>
                        <p class="text-secondary mt-3">
                            Дата заказа:<span class="float-right">{{ order.created_at }}</span>
                        </p>

                        <p class="text-secondary mt-3">
                            Сумма заказа:<span class="float-right">{{preparePrice(order.cost)}} руб.</span>
                        </p>
                        <p class="text-secondary" v-if="order.customer">
                            Покупатель:<span class="float-right">{{ order.customer.last_name }} {{ order.customer.first_name }} {{ order.customer.middle_name }}</span>
                        </p>
                    </div>
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
        ></customer-history-tab>
        <main-tab
                v-if="nav.currentTab === 'main'"
                :order="order"
        ></main-tab>
        <delivery-tab
                v-if="nav.currentTab === 'delivery'"
                :deliveries="order.deliveries"
        ></delivery-tab>
        <shipment-tab
                v-if="nav.currentTab === 'shipment'"
                :shipments="order.shipments"
        ></shipment-tab>
    </layout-main>
</template>

<script>

import {mapGetters} from "vuex";
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
        statusClass(statusId) {
            switch (statusId) {
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
