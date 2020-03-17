<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col>
                <b-card>
                    <infopanel :model.sync="customer"/>
                </b-card>
            </b-col>
            <b-col>
                <b-card>
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th colspan="4">KPIs</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Количество заказов</th>
                            <td>{{ order.count }}</td>
                        </tr>
                        <tr>
                            <th>Сумма заказов накопительным итогом</th>
                            <td>{{ order.price }}</td>
                        </tr>
                        </tbody>
                    </table>
                </b-card>
            </b-col>
        </b-row>

        <b-card no-body>
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <tab-main v-if="key === 'main'" :model.sync="customer" :order="order"/>
                    <tab-preference v-else-if="key === 'preference'" :id="customer.id"/>
                    <tab-order v-else-if="key === 'order'" :id="customer.id"/>
                    <tab-communication v-else-if="key === 'communication'" :customer="customer"/>
                    <tab-document v-else-if="key === 'document'" :id="customer.id"/>
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
        <modal-portfolios :model.sync="customer.portfolios" :customer-id="customer.id"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.problem" id="modal-mark-status-problem"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.temporarily_suspended" id="modal-mark-status-temporarily-suspended"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.block" id="modal-mark-status-block"/>
    </layout-main>
</template>

<script>

import VInput from '../../../components/controls/VInput/VInput.vue';

import TabMain from './components/tab-main.vue';
import TabPreference from './components/tab-preference.vue';
import TabOrder from './components/tab-order.vue';
import TabDocument from './components/tab-document.vue';
import TabCommunication from './components/tab-communication.vue';

import Services from '../../../../scripts/services/services.js';
import Infopanel from './components/infopanel.vue';
import ModalPortfolios from './components/modal-portfolios.vue';
import ModalMarkStatus from './components/modal-mark-status.vue';

export default {
    components: {
        TabCommunication, TabOrder, TabPreference, TabMain,
        ModalMarkStatus,
        ModalPortfolios,
        Infopanel, TabDocument, VInput},
    props: ['iCustomer', 'order'],
    data() {
        return {
            editStatus: false,
            customer: this.iCustomer,
            tabIndex: 0,
            showAllTabs: false,
        };
    },
    computed: {
        tabs() {
            let tabs = {};
            let i = -1;

            tabs.main = {i: i++, title: 'Информация'};
            if (this.customer.referral) {
                tabs.referralActive = {i: i++, title: 'Реферальная активность'};
                tabs.promoPage = {i: i++, title: 'Промостраница'};
                tabs.promoProduct = {i: i++, title: 'Товары  для продвижения'};
                tabs.orderReferrer = {i: i++, title: 'Реферальные заказы'};
                tabs.document = {i: i++, title: 'Акты'};
            }
            tabs.preference = {i: i++, title: 'Предпочтения'};
            tabs.subscribe = {i: i++, title: 'Подписки'};
            if (!this.customer.referral) {
                tabs.transaction = {i: i++, title: 'Транзакции'};
            }
            if (this.customer.referral) {
                tabs.billing = {i: i++, title: 'Биллинг'};
            }
            tabs.order = {i: i++, title: 'Заказы'};
            tabs.educationEvents = {i: i++, title: 'Образовательные события'};
            tabs.orderBack = {i: i++, title: 'Возвраты'};
            tabs.communication = {i: i++, title: 'Коммуникации'};
            tabs.review = {i: i++, title: 'Отзывы'};
            tabs.usedPromocodes = {i: i++, title: 'Промокоды и Скидки'};
            if (this.customer.referral) {
                tabs.referralPromocodes = {i: i++, title: 'Промокоды Реферального Партнера'};
            }
            tabs.gifts = {i: i++, title: 'Подарки'};
            tabs.sertificates = {i: i++, title: 'Сертификаты'};
            tabs.logSegment = {i: i++, title: 'Сегмент'};
            tabs.log = {i: i++, title: 'Логи'};

            return tabs;
        }
    },
    created() {
        Services.event().$on('showTab', (tab) => {
            this.tabIndex = this.tabs[tab];
        });
    }
};
</script>
