<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col>
                <b-card>
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th colspan="4">Инфопанель</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th>Роль</th>
                            <td colspan="3">
                                {{ customer.referral ? 'Реферальный Партнер' : 'Профессионал' }}
                                ({{ customer.role_date }})
                            </td>
                        </tr>
                        <tr>
                            <th>Статус</th>
                            <td>
                                <template v-if="!editStatus">
                                    {{ statuses[customer.status] }}
                                    <span class="align-middle float-right">
                                        <fa-icon icon="pencil-alt" @click="editStatus = customer.status"/>
                                    </span>
                                </template>
                                <template v-else>
                                    <div class="input-group input-group-sm">
                                        <select class="form-control form-control-sm" v-model="editStatus">
                                            <option v-for="(title, id) in statuses" :value="id">{{ title }}</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-success" @click="saveStatus"><fa-icon icon="save"/></button>
                                            <button class="btn btn-outline-danger" @click="editStatus = false">
                                                <fa-icon icon="times"/>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </td>
                            <th>Сегмент</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>ФИО</th>
                            <td colspan="3">{{ customer.full_name || '-' }}</td>
                        </tr>
                        <tr>
                            <th>ID</th>
                            <td>{{ customer.id }}</td>
                            <th>Фото</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>E-mail</th>
                            <td>{{ customer.email || '-' }}</td>
                            <th>Телефон</th>
                            <td>{{ customer.phone || '-' }}</td>
                        </tr>
                        <tr>
                            <th>Соцсети</th>
                            <td>
                                <div v-for="social in customer.socials">
                                    {{ social.driver }}: {{ social.name }}
                                </div>
                                <div v-if="!customer.socials.length">-</div>
                            </td>
                            <th>Ссылка на портфолио</th>
                            <td>
                                <div v-for="portfolio in customer.portfolios">
                                    <a :href="portfolio.link" target="_blank">{{ portfolio.name }}</a>
                                </div>
                                <div v-if="!customer.portfolios.length">-</div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
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
            <b-tabs lazy card>
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
import Services from '../../../../scripts/services/services.js';
import { mapGetters } from 'vuex';

export default {
    components: {CommunicationChatList, InfoLog, InfoSubscribe, InfoOrder, InfoPreference, InfoMain, VInput},
    props: ['iCustomer', 'statuses', 'order'],
    data() {
        return {
            editStatus: false,
            customer: this.iCustomer,
        };
    },
    computed: {
        ...mapGetters(['getRoute']),
    },
    methods: {
        saveStatus() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.save', {id: this.customer.id}), {}, {
                customer: {
                    status: this.editStatus,
                }
            }).then(data => {
                this.customer.status = this.editStatus;
                this.editStatus = false;
                Services.hideLoader();
            })
        },
    }
};
</script>
