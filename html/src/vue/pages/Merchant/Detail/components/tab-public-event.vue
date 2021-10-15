<template>
    <div>
        <h4>Отчеты</h4>
        <table>
            <tr>
                <td>
                    <date-picker
                        v-model="reportPeriod"
                        value-type="format"
                        format="YYYY-MM-DD"
                        range
                        input-class="form-control form-control-sm"
                    />
                </td>
                <td>
                    <button
                        class="btn btn-success btn-sm"
                        :disabled="!isReportPeriodSelected"
                        @click="downloadReport">Скачать отчет за период
                    </button>
                </td>
            </tr>
        </table>
        <hr>
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

export default {
    name: 'tab-public-event',
    props: ['model'],
    components: {
        DatePicker,
    },
    data() {
        return {
            reportPeriod: [],
            operations: [],
            currentPage: 1,
            pager: {},
        }
    },
    methods: {
        downloadReport() {
            Services.showLoader();

            let url = this.getRoute('merchant.detail.eventBillingList.downloadEventBillingList', {id: this.model.id});
            url += `?date_from=${this.reportPeriod[0]}&date_to=${this.reportPeriod[1]}`;

            window.open(url);

            this.reportPeriod = {dateFrom: null, dateTo: null};

            Services.hideLoader();
        },
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
    computed: {
        isReportPeriodSelected() {
            return this.reportPeriod
                && this.reportPeriod.length === 2
                && this.reportPeriod[0]
                && this.reportPeriod[1];
        },
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
