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
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <tab-digest v-if="key === 'digest'" :model.sync="merchant"/>
                    <tab-main v-else-if="key === 'main'" :model.sync="merchant"/>
                    <tab-store v-else-if="key === 'store'" :id="merchant.id"/>
                    <tab-commission v-else-if="key === 'commission'" :id="merchant.id"/>
                    <tab-operator v-else-if="key === 'operator'" :id="merchant.id"/>
                    <tab-order v-else-if="key === 'order'" :id="merchant.id"/>
                    <tab-product v-else-if="key === 'product'" :id="merchant.id"/>
                    <tab-communication v-else-if="key === 'communication'" :operators="merchant.operators"/>
                    <tab-marketing v-else-if="key === 'marketing'" :id="merchant.id" :legal_name="merchant.legal_name"/>
                    <tab-billing v-else-if="key === 'bill'" :model.sync="merchant"/>
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
import TabMain from './components/tab-main.vue';
import TabStore from './components/tab-store.vue';
import TabCommission from './components/tab-commission.vue';
import TabOperator from './components/tab-operator.vue';
import TabProduct from './components/tab-product.vue';
import TabOrder from './components/tab-order.vue';
import TabCommunication from './components/tab-communication.vue';
import TabMarketing from './components/tab-marketing.vue';
import TabBilling from "./components/tab-billing.vue";

export default {
    mixins: [tabsMixin],
    props: ['iMerchant', 'statuses', 'isRequest', 'ratings', 'managers'],
    components: {
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
    },
    data() {
        return {
            merchant: this.iMerchant,
        };
    },
    computed: {
        tabs() {
            let tabs = {};
            let i = 0;

            tabs.digest = {i: i, title: 'Дайджест'};
            tabs.main = {i: i++, title: 'Информация'};
            tabs.store = {i: i++, title: 'Склады'};
            tabs.commission = {i: i++, title: 'Комиссия'};
            tabs.operator = {i: i++, title: 'Команда мерчанта'};
            tabs.product = {i: i++, title: 'Товары'};
            tabs.order = {i: i++, title: 'Заказы'};
            tabs.return = {i: i++, title: 'Возвраты'};
            tabs.publicEvent = {i: i++, title: 'Мастер-классы'};
            tabs.client = {i: i++, title: 'Клиенты'};
            tabs.communication = {i: i++, title: 'Коммуникации'};
            tabs.marketing = {i: i++, title: 'Маркетинг'};
            tabs.bill = {i: i++, title: 'Биллинг'};
            tabs.report = {i: i++, title: 'Отчеты'};
            tabs.eDocument = {i: i++, title: 'Электронный документооборот'};
            tabs.log = {i: i++, title: 'Логи'};

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
