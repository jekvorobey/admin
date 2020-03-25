<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col>
                <b-card>
                    <infopanel :model.sync="deliveryService" :delivery-service-statuses="deliveryServiceStatuses"/>
                </b-card>
            </b-col>
            <b-col>
                <b-card>
                    <div class="row">
                        <div class="col">
                            <p class="font-weight-bold">KPIs</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <p class="font-weight-bold">Количество отправлений</p>
                        </div>
                        <div class="col">
                            <div class="float-right">{{ shipmentsInfo.allCount }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <p class="font-weight-bold">Сумма отправлений</p>
                        </div>
                        <div class="col">
                            <div class="float-right">{{ shipmentsInfo.allPrice }}</div>
                        </div>
                    </div>
                </b-card>
            </b-col>
        </b-row>

        <b-card no-body>
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <tab-settings v-if="key === 'settings'" :model.sync="deliveryService"/>
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

    import Services from '../../../../../scripts/services/services.js';
    import Infopanel from './components/infopanel.vue';
    import TabSettings from './components/tab-settings.vue';

    export default {
    components: {
        Infopanel,
        VInput,
        TabSettings,
    },
    props: [
        'iDeliveryService',
        'deliveryServiceStatuses',
        'shipmentsInfo',
    ],
    data() {
        return {
            editStatus: false,
            deliveryService: this.iDeliveryService,
            tabIndex: 0,
            showAllTabs: false,
        };
    },
    watch: {
        'tabIndex': 'pushRoute',
        'showAllTabs': 'pushRoute',
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
        currentTabName() {
            let tabName = 'main';
            for (let key in this.tabs) {
                if (!this.tabs.hasOwnProperty(key)) {
                    continue;
                }
                if (this.tabs[key].i === this.tabIndex) {
                    tabName = key;
                }
            }

            return tabName;
        }
    },
    methods: {
        pushRoute() {
            let route = {};
            if (this.level_id) {
                route.level_id = this.level_id;
            }
            Services.route().push({
                tab: this.currentTabName,
                allTab: this.showAllTabs ? 1 : 0,
            }, this.getRoute('deliveryService.detail', {id: this.deliveryService.id}));
        },
    },
    created() {
        Services.event().$on('showTab', (tab) => {
            this.tabIndex = this.tabs[tab];
        });

        let currentTab = Services.route().get('tab', 'main');
        this.showAllTabs = !!Number(Services.route().get('allTab', 0));
        this.tabIndex = this.tabs[currentTab].i;
    }
};
</script>
