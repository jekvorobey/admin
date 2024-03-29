<template>
    <div>
        <billing-report
            :model.sync="model"
            :type="billingReportTypes.public_events"
            title="Отчеты агента"
            :rightsBlock="blocks.merchants"
            :withEditCycle="false"
        ></billing-report>

        <hr>
        <h4>Заказы</h4>
        <div class="table-responsive">
            <table class="table table-condensed">
                <thead>
                <tr>
                    <th>Номер Заказа</th>
                    <th>Дата продажи</th>
                    <th>Название</th>
                    <th>Количество</th>
                    <th>Цена за единицу, р</th>
                    <th>Сумма</th>
                    <th>Скидка</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="operation in operations">
                    <td>
                        <a :href="$store.getters.getRoute('orders.detail', {id: operation.id})">
                            {{ operation.number }}
                        </a>
                    </td>
                    <td>{{ operation.created_at }}</td>
                    <td>
                        <p v-for="item in operation.basket.items">{{ item.name }}</p>
                    </td>
                    <td>{{ operation.count_tickets }}</td>
                    <td v-if="operation.count_tickets > 0">
                        {{ operation.price / operation.count_tickets }}
                    </td>
                    <td v-else>0</td>
                    <td>{{ parseInt(operation.price) }}</td>
                    <td>{{ operation.cost - operation.price }}</td>
                    <td>
                        <b-badge v-if="operation.is_canceled === 1" variant="danger">Отменен</b-badge>
                        <b-badge v-if="operation.is_canceled === 0" variant="success">Доставлен</b-badge>
                    </td>
                </tr>
                <tr v-if="!operations.length">
                    <td colspan="8" class="text-center">Заказы отсутствуют</td>
                </tr>
                </tbody>
            </table>
        </div>
        <b-pagination
            v-if="pager.last_page > 1"
            v-model="currentPage"
            :total-rows="pager.total"
            :per-page="pager.per_page"
            :hide-goto-end-buttons="pager.last_page < 10"
            class="float-right"
        ></b-pagination>
    </div>
</template>

<script>
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import 'vue2-datepicker/locale/ru.js';
import Services from "../../../../../scripts/services/services";
import BillingReport from "../../../../components/billing-report/billing-report.vue";

export default {
    name: 'tab-public-event',
    props: ['model'],
    components: {
        DatePicker,
        BillingReport,
    },
    data() {
        return {
            operations: [],
            currentPage: 1,
            pager: {},
        }
    },
    methods: {
        paginationPromise() {
            return Services.net().get(
                this.getRoute('merchant.detail.eventBillingList', {id: this.model.id}),
                {
                    page: this.currentPage,
                }
            );
        },
        loadPage() {
            Services.showLoader();

            Services.net().get(this.getRoute('merchant.detail.eventBillingList', {id: this.model.id}), {
                page: this.currentPage,
            })
                .then(data => {
                    this.operations = data.billingList.items || [];
                    this.setPager(data.billingList);
                })
                .finally(() => {
                    Services.hideLoader();
                });
        },
        setPager(data) {
            if (data.pager) {
                this.pager = {
                    last_page: data.pager.pages,
                    total: data.pager.total,
                    current_page: data.pager.current_page,
                    per_page: data.pager.pageSize,
                };
            }
        },
    },
    created() {
        this.loadPage();
    },
    watch: {
        currentPage() {
            this.loadPage();
        }
    }
};
</script>
<style lang="css">
.mx-datepicker-popup {
    overflow: visible !important;
    z-index: 9999;
}
</style>
