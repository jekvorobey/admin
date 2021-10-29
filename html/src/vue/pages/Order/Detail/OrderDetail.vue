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

        <b-card no-body>
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <tab-main v-if="key === 'main'" :model.sync="order"/>
                    <tab-composition v-else-if="key === 'composition'" :model.sync="order"/>
                    <tab-deliveries v-else-if="key === 'deliveries'" :model.sync="order"/>
                    <tab-shipments v-else-if="key === 'shipments'" :model.sync="order, barcodes"/>
                    <tab-customer-order-history v-else-if="key === 'customer_order_history'" :model.sync="order.customer_history"/>
                    <tab-logs v-else-if="key === 'logs'" :model.sync="order.history"/>
                    <template v-else>
                      Заглушка
                    </template>
                </b-tab>
                <template v-slot:tabs-end>
                    <b-nav-item @click.prevent="showAllTabs = !showAllTabs" href="#">
                        <b>{{ showAllTabs ? '-' : '+' }}</b>
                    </b-nav-item>
                </template>
            </b-tabs>
        </b-card>
    </layout-main>
</template>

<script>
    import Infopanel from './components/infopanel.vue';
    import Kpi from './components/kpi.vue';
    import TabMain from './components/tab-main.vue';
    import TabComposition from './components/tab-composition.vue';
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
            TabDeliveries,
            TabShipments,
            TabCustomerOrderHistory,
            TabLogs,
        },
        props: {
            iOrder: {},
            kpis: {},
        },
        data() {
            return {
                order: this.iOrder,
                barcodes: this.iOrder.barcodes,
            };
        },

        methods: {

        },

        computed: {
            tabs() {
                let tabs = {};
                let i = 0;

                tabs.main = {i: i++, title: 'Дайджест'};
                tabs.composition = {i: i++, title: 'Состав заказа'};
                tabs.deliveries = {i: i++, title: 'Доставки'};
                tabs.shipments = {i: i++, title: 'Отправления'};
                tabs.returns = {i: i++, title: 'Возвраты'};
                tabs.customer_order_history = {i: i++, title: 'История заказов клиента'};
                tabs.logs = {i: i++, title: 'Логи'};

                return tabs;
            },
        },
    };
</script>
