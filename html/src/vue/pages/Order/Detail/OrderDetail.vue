<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col class="col-12 col-md-8 mb-2">
                <infopanel :model.sync="order"/>
            </b-col>
            <b-col class="col-12 col-md-4 mb-2">
                <kpi :kpis="kpis"/>
            </b-col>
        </b-row>

        <b-card no-body v-if="canView(blocks.orders)">
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <tab-main v-if="key === 'main'" :model.sync="order"/>
                    <tab-composition v-else-if="key === 'composition'" :model.sync="order"/>
                    <tab-composition-event v-else-if="key === 'composition_event'" :model.sync="orderInfo"/>
                    <tab-deliveries v-else-if="key === 'deliveries'" :model.sync="order"/>
                    <tab-shipments v-else-if="key === 'shipments'" :model.sync="order"/>
                    <tab-customer-order-history v-else-if="key === 'customer_order_history'"
                                                :model.sync="order.customer_history"/>
                    <tab-logs v-else-if="key === 'logs'" :model.sync="order.history"/>
                    <template v-else>
                        Заглушка
                    </template>
                </b-tab>
            </b-tabs>
        </b-card>
    </layout-main>
</template>

<script>
import Infopanel from './components/infopanel.vue';
import Kpi from './components/kpi.vue';
import TabMain from './components/tab-main.vue';
import TabComposition from './components/tab-composition.vue';
import TabCompositionEvent from './components/tab-composition-event.vue';
import TabDeliveries from './components/tab-deliveries.vue';
import TabShipments from './components/tab-shipments.vue';
import TabCustomerOrderHistory from './components/tab-customer-order-history.vue';
import TabLogs from './components/tab-logs.vue';
import tabsMixin from "../../../mixins/tabs";

export default {
    mixins: [tabsMixin],
    components: {
        Infopanel,
        Kpi,
        TabMain,
        TabComposition,
        TabCompositionEvent,
        TabDeliveries,
        TabShipments,
        TabCustomerOrderHistory,
        TabLogs,
    },
    props: {
        iOrder: {},
        iOrderInfo: {},
        kpis: {},
    },
    data() {
        return {
            order: this.iOrder,
            orderInfo: this.iOrderInfo,
        };
    },

    methods: {},

    computed: {
        tabs() {
            let tabs = {};
            let i = 0;
            if (this.order.basket.type === this.basketTypes.product) {
                tabs.main = {i: i++, title: 'Дайджест'};
                tabs.composition = {i: i++, title: 'Состав заказа'};
                tabs.deliveries = {i: i++, title: 'Доставки'};
                tabs.shipments = {i: i++, title: 'Отправления'};
                tabs.returns = {i: i++, title: 'Возвраты'};
                tabs.customer_order_history = {i: i++, title: 'История заказов клиента'};
                tabs.logs = {i: i++, title: 'Логи'};
            } else {
                tabs.composition_event = {i: i++, title: 'Состав заказа МК'};
            }

            return tabs;
        },
    },
};
</script>
