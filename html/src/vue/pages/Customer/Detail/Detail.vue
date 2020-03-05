<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col>
                <b-card>
                    <infopanel :model.sync="customer" :statuses="statuses" :status-problem="statusProblem"/>
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
                <b-tab title="Основная информация">
                    <info-main :model.sync="customer" :order="order"/>
                </b-tab>
                <b-tab title="Предпочтения">
                    <info-preference :id="customer.id"/>
                </b-tab>
                <b-tab title="Заказы">
                    <info-order :id="customer.id"/>
                </b-tab>
                <b-tab title="Коммуникации">
                    <communication-chat-creator :user_id="customer.user_id"/>
                    <communication-chat-list :filter="{user_id: customer.user_id}"/>
                </b-tab>
                <b-tab title="Подписки">
                    <info-subscribe :id="customer.id"/>
                </b-tab>
                <b-tab title="Логи">
                    <info-log :id="customer.id"/>
                </b-tab>
            </b-tabs>
        </b-card>
        <modal-portfolios :model.sync="customer.portfolios" :customer-id="customer.id"/>
        <modal-mark-problem :model.sync="customer" :status-problem="statusProblem"/>
    </layout-main>
</template>

<script>

import VInput from '../../../components/controls/VInput/VInput.vue';
import InfoMain from './components/info-main.vue';
import InfoPreference from './components/info-preference.vue';
import InfoOrder from './components/info-order.vue';
import InfoSubscribe from './components/info-subscribe.vue';
import InfoLog from './components/info-log.vue';
import CommunicationChatList from '../../../components/communication-chat-list/communication-chat-list.vue';
import CommunicationChatCreator from '../../../components/communication-chat-creator/communication-chat-creator.vue';
import Services from '../../../../scripts/services/services.js';
import Infopanel from './components/infopanel.vue';
import ModalPortfolios from './components/modal-portfolios.vue';
import ModalMarkProblem from './components/modal-mark-problem.vue';

export default {
    components: {
        ModalMarkProblem,
        ModalPortfolios,
        Infopanel,
        CommunicationChatList,
        CommunicationChatCreator,
        InfoLog,
        InfoSubscribe,
        InfoOrder,
        InfoPreference,
        InfoMain,
        VInput},
    props: ['iCustomer', 'statuses', 'order', 'statusProblem'],
    data() {
        return {
            editStatus: false,
            customer: this.iCustomer,
            tabIndex: 0,
            tabs: {
                main: 0,
                preference: 1,
                order: 2,
                'chat-list': 3,
                subscribe: 4,
                log: 5,
            }
        };
    },
    created() {
        Services.event().$on('showTab', (tab) => {
            this.tabIndex = this.tabs[tab];
        });
    }
};
</script>
