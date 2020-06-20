<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col>
                <b-card>
                    <infopanel :model.sync="customer" :referral-levels="referralLevels"/>
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
                    <tab-subscriptions v-else-if="key === 'subscriptions'" :model.sync="customer"/>
                    <tab-document v-else-if="key === 'document'" :model.sync="customer"/>
                    <tab-promo-product v-else-if="key === 'promoProduct'" :id="customer.id"/>
                    <tab-promo-page v-else-if="key === 'promoPage'" :model.sync="customer"/>
                    <tab-order-referrer v-else-if="key === 'orderReferrer'" :id="customer.id"/>
                    <tab-billing v-else-if="key === 'billing'" :model.sync="customer"/>
                    <tab-bonus v-else-if="key === 'bonuses'" :model.sync="customer"/>
                    <tab-promocodes v-else-if="key === 'referralPromocodes'" :id="customer.id" :options="options"/>
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
        <modal-mark-status :model.sync="customer" :status="customerStatus.active" id="modal-mark-status-active"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.created" id="modal-mark-status-created"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.new" id="modal-mark-status-new"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.consideration" id="modal-mark-status-consideration"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.rejected" id="modal-mark-status-rejected"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.potential_rp" id="modal-mark-status-potential_rp"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.active" id="modal-mark-status-active"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.temporarily_suspended" id="modal-mark-status-temporarily-suspended"/>
        <modal-mark-status :model.sync="customer" :status="customerStatus.block" id="modal-mark-status-block"/>
    </layout-main>
</template>

<script>

import VInput from '../../../components/controls/VInput/VInput.vue';

import Infopanel from './components/infopanel.vue';
import ModalPortfolios from './components/modal-portfolios.vue';
import ModalMarkStatus from './components/modal-mark-status.vue';

import TabMain from './components/tab-main.vue';
import TabPreference from './components/tab-preference.vue';
import TabOrder from './components/tab-order.vue';
import TabDocument from './components/tab-document.vue';
import TabSubscriptions from "./components/tab-subscriptions.vue";
import TabCommunication from './components/tab-communication.vue';
import TabPromoProduct from './components/tab-promo-product.vue';
import TabPromoPage from './components/tab-promo-page.vue';
import TabOrderReferrer from './components/tab-order-referrer.vue';
import TabBilling from './components/tab-billing.vue';
import TabBonus from './components/tab-bonus.vue';
import TabPromocodes from './components/tab-promocodes.vue';

import tabsMixin from '../../../mixins/tabs.js';
import Services from "../../../../scripts/services/services";

export default {
    mixins: [tabsMixin],
    props: [
        'iCustomer',
        'order',
        'referralLevels',
        'options',
        'unreadMsgCount'
    ],
    components: {
        TabSubscriptions,
        TabBilling,
        TabCommunication,
        TabMain,
        TabOrder,
        TabOrderReferrer,
        TabPreference,
        TabPromoPage,
        TabPromoProduct,
        TabBonus,
        TabPromocodes,
        ModalMarkStatus,
        ModalPortfolios,
        Infopanel,
        TabDocument,
        VInput
    },
    data() {
        return {
            editStatus: false,
            customer: this.iCustomer,
        };
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
    },
    computed: {
        tabs() {
            let tabs = {};
            let i = 0;
            let unreadMsgIndicator = this.unreadMsgCount > 0 ?
                ` (${this.unreadMsgCount})` : '';

            tabs.main = {i: i++, title: 'Информация'};
            if (this.customer.referral) {
                tabs.promoPage = {i: i++, title: 'Промостраница'};
                tabs.promoProduct = {i: i++, title: 'Товары  для продвижения'};
                tabs.orderReferrer = {i: i++, title: 'Реферальные заказы'};
                if (this.showAllTabs) {
                    tabs.document = {i: i++, title: 'Акты'};
                }
            }
            if (this.showAllTabs) {
                tabs.preference = {i: i++, title: 'Предпочтения'};
                tabs.subscriptions = {i: i++, title: 'Подписки'};
            }
            if (!this.customer.referral) {
                tabs.transaction = {i: i++, title: 'Транзакции'};
            }
            if (this.customer.referral) {
                tabs.billing = {i: i++, title: 'Биллинг'};
            }
            tabs.order = {i: i++, title: 'Заказы'};
            if (this.showAllTabs) {
                tabs.educationEvents = {i: i++, title: 'Образовательные события'};
                tabs.orderBack = {i: i++, title: 'Возвраты'};
                tabs.communication = {i: i++, title: 'Коммуникации'+unreadMsgIndicator};
                tabs.review = {i: i++, title: 'Отзывы'};
                tabs.usedPromocodes = {i: i++, title: 'Промокоды и Скидки'};
            }
            if (this.customer.referral) {
                tabs.referralPromocodes = {i: i++, title: 'Промокоды Реферального Партнера'};
            }
            if (this.showAllTabs) {
                tabs.bonuses = {i: i++, title: 'Бонусы'};
                tabs.gifts = {i: i++, title: 'Подарки'};
                tabs.sertificates = {i: i++, title: 'Сертификаты'};
                tabs.logSegment = {i: i++, title: 'Сегмент'};
                tabs.log = {i: i++, title: 'Логи'};
            }

            return tabs;
        },
    },
};
</script>
