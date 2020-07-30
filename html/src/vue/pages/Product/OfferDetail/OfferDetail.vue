<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col class="col-7">
                <infopanel
                        :offer="offer"
                ></infopanel>
            </b-col>
            <b-col>
                <kpi-panel
                        :kpi="kpi"
                ></kpi-panel>
            </b-col>
        </b-row>

        <v-tabs :current="nav.currentTab" :items="nav.tabs" @nav="tab => nav.currentTab = tab"></v-tabs>
    </layout-main>
</template>

<script>
    import VTabs from '../../../components/tabs/tabs.vue';
    import Infopanel from './components/infopanel.vue';
    import KpiPanel from  './components/kpis-panel.vue';
    // import StocksTab from './components/tab-stocks.vue';
    // import PricesTab from './components/tab-prices.vue';
    // import HistoryTab from './components/tab-history.vue';
    // import Services from "../../../../../scripts/services/services";

    export default {
        components: {
            VTabs,
            Infopanel,
            KpiPanel,
            // StocksTab,
            // PricesTab,
            // HistoryTab,
        },
        props: {
            offer: {},
            kpi: {},
        },
        data() {
            return {
                nav: {
                    currentTab: 'stocks',
                    tabs: [
                        {value: 'stocks', text: 'Остатки'},
                        {value: 'prices', text: 'Цены'},
                        {value: 'history', text: 'История'},
                    ]
                }
            };
        },
        methods: {

        }
    };
</script>