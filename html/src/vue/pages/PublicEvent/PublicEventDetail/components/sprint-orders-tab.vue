<template>
    <div>
        <span>Спринт</span>
        <b-form-select v-model="sprintIdModel" text-field="interval" value-field="id" :options="sprints">
            <template #first>
                <b-form-select-option :value="null">-- Выберите спринт --</b-form-select-option>
            </template>
        </b-form-select>
        <br/>
        <table class="table">
            <thead>
                <tr>
                    <th>ID заказа</th>
                    <th>Дата заказа</th>
                    <th>Кол-во билетов</th>
                    <th>Сумма заказа</th>
                    <th>Покупателя</th>
                    <th>Статус</th>
                    <th class="text-right" v-if="canUpdate(blocks.events)">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="order in orders" :key="order.id">
                    <td>
                        <a :href="getRoute('orders.detail', {id: order.id})">{{order.number}}</a>
                    </td>
                    <td>{{ order.created_at }}</td>
                    <td>{{order.count_tickets}}</td>
                    <td>{{preparePrice(order.price) + ' руб.'}}</td>
                    <td>
                        <a v-if="order.customer_id" :href="getRoute('customers.detail', {id: order.customer_id})">
                            {{order.customer ? order.customer.full_name : order.customer_id }}
                        </a>
                    </td>
                    <td>
                        <order-status :status='order.status'/>
                        <template v-if="order.is_canceled">
                            <br><span class="badge badge-danger">Отменен</span>
                        </template>
                        <template v-if="order.is_problem">
                            <br><span class="badge badge-danger">Проблемный</span>
                        </template>
                        <template v-if="order.is_require_check">
                            <br><span class="badge badge-danger">Требует проверки</span>
                        </template>
                    </td>
                    <td v-if="canUpdate(blocks.events)"></td>
                </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="pager.pages > 1"
                v-model="page"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                :hide-goto-end-buttons="pager.pages < 10"
                class="mt-3 float-right"
        />
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {
        ACT_LOAD_SPRINTS,
        ACT_LOAD_SPRINT_ORDERS,
        NAMESPACE
    } from '../../../../store/modules/public-events';

    import modalMixin from '../../../../mixins/modal';
    import mediaMixin from '../../../../mixins/media';
    import Helpers from "../../../../../scripts/helpers";

    export default {
        mixins: [
            modalMixin,
            mediaMixin
        ],
        props: {
            publicEvent: {},
            sprintId: null,
        },
        data() {
            return {
                sprintIdModel: this.sprintId,
                sprints: [],
                orders: [],
                pager: {
                    pages: 1,
                    total: 10,
                },
                page: 1,
            };
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadSprints: ACT_LOAD_SPRINTS,
                loadOrders: ACT_LOAD_SPRINT_ORDERS,
            }),
            reload() {
                this.loadOrders({publicEventId: this.publicEvent.id, page: this.page, sprintId: this.sprintIdModel})
                    .then(response => {
                        this.pager = response.orders.pager;
                        this.orders = response.orders.orders;
                        this.page = response.orders.page;
                    });
                this.loadSprints({publicEventId: this.publicEvent.id})
                    .then(response => {
                        this.sprints = response.sprints;
                        if (this.sprints.length) {
                            this.sprints.forEach(sprint => {
                                sprint.interval = this.interval(sprint.date_start, sprint.date_end);
                            });
                        }
                    });
            },
            interval(dateStartString, dateEndString) {
                return Helpers.onlyDate(dateStartString) + ' - ' + Helpers.onlyDate(dateEndString);
            },
        },
        created() {
            this.reload();
        },
        watch: {
            'page': 'reload',
            'sprintIdModel': 'reload',
        },
    }
</script>

<style lang="css">
.mx-datepicker-popup {
    overflow: visible !important;
    z-index: 9999;
}
.preview {
    width: 550px;
}
</style>
