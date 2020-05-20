<template>
    <layout-main back hide-title>
        <b-row class="mb-2">
            <b-col class="col-12 col-md-6 mb-2">
                <infopanel :model.sync="deliveryService" :delivery-service-statuses="deliveryServiceStatuses"/>
            </b-col>
            <b-col class="col-12 col-md-6">
                <kpi :shipments-info="shipmentsInfo"/>
            </b-col>
        </b-row>

        <b-card no-body>
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <tab-settings v-if="key === 'settings'" :model.sync="deliveryService"/>
                    <tab-limitations v-else-if="key === 'limitations'" :model.sync="deliveryService"/>
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
    import VInput from '../../../../components/controls/VInput/VInput.vue';

    import Infopanel from './components/infopanel.vue';
    import Kpi from './components/kpi.vue';
    import TabSettings from './components/tab-settings.vue';
    import TabLimitations from './components/tab-limitations.vue';
    import tabsMixin from "../../../../mixins/tabs";

    export default {
        mixins: [tabsMixin],
        components: {
            Infopanel,
            Kpi,
            VInput,
            TabSettings,
            TabLimitations,
        },
        props: [
            'iDeliveryService',
            'deliveryServiceStatuses',
            'shipmentsInfo',
        ],
        data() {
            return {
                deliveryService: this.iDeliveryService,
            };
        },
        computed: {
            tabs() {
                let tabs = {};
                let i = 0;

                tabs.main = {i: i++, title: 'Информация'};
                tabs.settings = {i: i++, title: 'Настройки'};
                tabs.limitations = {i: i++, title: 'Ограничения'};
                tabs.shipments = {i: i++, title: 'Отправления'};
                tabs.points = {i: i++, title: 'Пункты выдачи/приема заказов'};
                if (this.showAllTabs) {
                    tabs.documents = {i: i++, title: 'Документы'};
                    tabs.managers = {i: i++, title: 'Менеджеры'};
                    tabs.cities = {i: i++, title: 'Населенные пункты доставки'};
                    tabs.cargos = {i: i++, title: 'Грузы'};
                    tabs.cargos = {i: i++, title: 'Задания на забор'};
                    tabs.cargos = {i: i++, title: 'Задания на доставку'};
                    tabs.cargos = {i: i++, title: 'Заявки на расконсолидацию'};
                    tabs.reports = {i: i++, title: 'Отчеты'};
                    tabs.orderStatuses = {i: i++, title: 'Модель статусов'};
                    tabs.pickupTimes = {i: i++, title: 'Графики приезда к мерчантам'};
                }

                return tabs;
            },
        },
    };
</script>
