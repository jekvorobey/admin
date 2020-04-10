<template>
    <layout-main back>
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
                    <tab-main v-if="key === 'main'" :model.sync="merchant"/>
                    <tab-operator v-else-if="key === 'operator'" :id="merchant.id"/>
                    <tab-commission v-else-if="key === 'commission'" :id="merchant.id"/>
                    <template v-else>
                        Заглушка
                    </template>
                </b-tab>
            </b-tabs>
        </b-card>
    </layout-main>
</template>

<script>
import TabOperator from './components/tab-operator.vue';
import Infopanel from './components/infopanel.vue';
import TabMain from './components/tab-main.vue';

import tabsMixin from '../../../mixins/tabs.js';
import TabCommission from './components/tab-commission.vue';

export default {
    mixins: [tabsMixin],
    props: ['iMerchant', 'statuses', 'isRequest', 'ratings', 'managers'],
    components: {
        TabCommission,
        TabMain,
        Infopanel,
        TabOperator,
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

            tabs.digest = {i: i++, title: 'Дайджест'};
            tabs.main = {i: i++, title: 'Информация'};
            tabs.storage = {i: i++, title: 'Склады'};
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
