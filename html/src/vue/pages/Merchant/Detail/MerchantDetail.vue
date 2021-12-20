<template>
    <layout-main back :back-url="getRoute('merchant.registrationList')">
        <b-row class="mb-2">
            <b-col>
                <b-card>
                    <infopanel
                        :model.sync="merchant"
                        :statuses="statuses"
                        :ratings="ratings"
                        :managers="managers"
                        :is-request="isRequest"
                    />
                </b-card>
            </b-col>
            <b-col>
                <b-card></b-card>
            </b-col>
        </b-row>

        <b-card no-body>
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" v-if="filterTab(key)"  :title="tab.title">
                    <tab-digest v-if="key === 'digest'" :model.sync="merchant"/>
                    <tab-public-event v-if="key === 'publicEvent'" :model.sync="merchant"/>
                    <tab-main v-else-if="key === 'main'" :model.sync="merchant" :brand-list="brandList" :category-list="categoryList"/>
                    <tab-store v-else-if="key === 'store'" :id="merchant.id"/>
                    <tab-commission v-else-if="key === 'commission'" :id="merchant.id" :brand-list="brandList" :category-list="categoryList"/>
                    <tab-taxes v-else-if="key === 'taxes'" :id="merchant.id" :brand-list="brandList" :category-list="categoryList"/>
                    <tab-operator v-else-if="key === 'operator'" :id="merchant.id"/>
                    <tab-order v-else-if="key === 'order'" :id="merchant.id"/>
                    <tab-product v-else-if="key === 'product'" :id="merchant.id"/>
                    <tab-communication v-else-if="key === 'communication'" :operators="merchant.operators"/>
                    <tab-marketing v-else-if="key === 'marketing'" :id="merchant.id" :legal_name="merchant.legal_name"/>
                    <tab-billing v-else-if="key === 'bill'" :model.sync="merchant"/>
                    <tab-ext-systems v-else-if="key === 'extSystems'" :id="merchant.id"/>
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

import tabsMixin from '../../../mixins/tabs.js';
import TabDigest from './components/tab-digest.vue';
import TabPublicEvent from './components/tab-public-event.vue';
import TabMain from './components/tab-main.vue';
import TabStore from './components/tab-store.vue';
import TabCommission from './components/tab-commission.vue';
import TabOperator from './components/tab-operator.vue';
import TabProduct from './components/tab-product.vue';
import TabOrder from './components/tab-order.vue';
import TabCommunication from './components/tab-communication.vue';
import TabMarketing from './components/tab-marketing.vue';
import TabBilling from "./components/tab-billing.vue";
import TabTaxes from "./components/tab-taxes.vue";
import TabExtSystems from "./components/tab-ext-systems.vue";
import Services from "../../../../scripts/services/services";

export default {
    mixins: [tabsMixin],
    props: [
        'iMerchant',
        'statuses',
        'isRequest',
        'ratings',
        'managers',
        'unreadMsgCount',
        'brandList',
        'categoryList',
    ],
    components: {
        TabExtSystems,
        TabPublicEvent,
        TabBilling,
        Infopanel,
        TabDigest,
        TabMain,
        TabStore,
        TabCommission,
        TabOperator,
        TabProduct,
        TabOrder,
        TabCommunication,
        TabMarketing,
        TabTaxes,
    },
    data() {
        return {
            merchant: this.iMerchant,
        };
    },
    created() {
        let currentTab = Services.route().get('tab', 'digest');
        this.tabIndex = this.tabs[currentTab].i;
    },
    methods: {
        pushRoute() {
            let route = {};
            if (this.level_id) {
                route.level_id = this.level_id;
            }

            let tmp = [];
            let params = location.search
                .substr(1)
                .split("&");
            let paramShowTab = params.filter(function (item) {
                tmp = item.split("=");
                return tmp[0] === 'showChat';
            });

            if (!(Array.isArray(paramShowTab) && paramShowTab.length)) {
                Services.route().push({
                    tab: this.currentTabName,
                    allTab: this.showAllTabs ? 1 : 0,
                }, location.pathname);
            }
        },
      filterTab(key) {
        const commissionaireTabs = ['store', 'product', 'billing', 'extSystems'];
        const agentTabs = ['publicEvent'];
        const commissionaireFieldsFilled = this.merchant.commissionaire_contract_number && this.merchant.commissionaire_contract_at;
        const agentFieldsFilled = this.merchant.agent_contract_number && this.merchant.agent_contract_at;

        return !commissionaireTabs.concat(agentTabs).includes(key)
            || ( commissionaireTabs.includes(key) && commissionaireFieldsFilled )
            || ( agentTabs.includes(key) && agentFieldsFilled );
      }
    },
    computed: {
        tabs() {
            let tabs = {};
            let i = 0;
            let unreadMsgIndicator = this.unreadMsgCount > 0 ?
                ` (${this.unreadMsgCount})` : '';

            tabs.digest = {i: i++, title: 'Дайджест'};
            tabs.main = {i: i++, title: 'Информация'};
            tabs.store = {i: i++, title: 'Склады'};
            tabs.commission = {i: i++, title: 'Комиссия'};
            tabs.taxes = {i: i++, title: 'Ставка НДС'};
            tabs.operator = {i: i++, title: 'Команда мерчанта'};
            tabs.product = {i: i++, title: 'Товары'};
            tabs.order = {i: i++, title: 'Заказы'};
            tabs.return = {i: i++, title: 'Возвраты'};
            tabs.publicEvent = {i: i++, title: 'Мастер-классы'};
            tabs.client = {i: i++, title: 'Клиенты'};
            tabs.communication = {i: i++, title: 'Коммуникации'+unreadMsgIndicator};
            tabs.marketing = {i: i++, title: 'Маркетинг'};
            tabs.bill = {i: i++, title: 'Биллинг'};
            tabs.log = {i: i++, title: 'Логи'};
            tabs.extSystems = {i: i++, title: 'Интеграция'};

            return tabs;
        },
    },
};
</script>

<style>
    .highlighted{
        background: #ffeebc;
        box-shadow: 0 0 0 0.2rem rgba(222, 170, 12, 0.5);
    }
</style>
