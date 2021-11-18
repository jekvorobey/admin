<template>
    <div>
        <h4>Биллинговый период</h4>
        <span class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="monthly-billing_cycle" key="monthly" v-model="monthly">
            <label class="custom-control-label" for="monthly-billing_cycle"></label>
            <label for="monthly-billing_cycle">Календарный месяц</label>
        </span>
        <div v-if="!monthly">
            <div class="row">
                <v-input
                    v-model="form.billing_cycle"
                    class="col-4"
                    type="number"
                    min="1"
                    step="1"
                    help="период указывается в днях">Произвольный период
                </v-input>

                <div class="col-12" v-if="checkRights">
                    <button class="btn btn-sm btn-success" :disabled="form.billing_cycle <= 0" @click="saveBillingCycle">
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
        <hr>

        <h4>Отчеты</h4>
        <table>
            <tr>
                <td>
                    <date-picker
                        v-model="billing.period"
                        value-type="format"
                        format="YYYY-MM-DD"
                        range
                        input-class="form-control form-control-sm"
                    />
                </td>
                <td>
                    <button
                        class="btn btn-success btn-sm"
                        :disabled="!isPeriod(billing.period)"
                        @click="createReport">Сделать внеочередной биллинг
                    </button>
                </td>
            </tr>
        </table>

        <hr>
        <h4 class="mt-4">{{ title }}</h4>
        <table class="table mt-2">
            <tr>
                <td>Период</td>
                <td>Статус</td>
                <td>К выплате, р</td>
                <td>Документ</td>
                <td>Создан</td>
                <td>Изменен</td>
                <td v-if="checkRights">Действия</td>
            </tr>
            <tr v-for="report in billingReports">
                <td>{{ datePrint(report.date_from) }} &ndash; {{ datePrint(report.date_to) }}</td>
                <td>
                    <b-badge :variant="getBadge(report.status)">
                        {{ getStatus(report.status) }}
                    </b-badge>
                </td>
                <td>{{ report.total_sum.toLocaleString() }}</td>
                <td>
                    <a target="_blank" :href="$store.getters.getRoute('billingReport.detail.download',
          {entityId:getEntityId, reportId:report.id, type: getType})">Скачать</a>
                </td>
                <td>{{ datetimePrint(report.created_at) }}</td>
                <td>{{ datetimePrint(report.updated_at) }}</td>
                <td v-if="checkRights">
                    <b-button v-if="report.status === billingReportStatus.new" class="btn btn-warning btn-sm" @click="updateStatus(report.id, billingReportStatus.waiting)">
                        Отправить
                        <fa-icon icon="check"/>
                    </b-button>
                    <b-button v-else-if="report.status !== billingReportStatus.accepted" class="btn btn-success btn-sm"
                              @click="updateStatus(report.id, billingReportStatus.accepted)">
                        Подтвердить
                        <fa-icon icon="check"/>
                    </b-button>
                    <b-button class="btn btn-danger btn-sm" @click="removeItem(report.id)">
                        Удалить
                        <fa-icon icon="trash-alt"/>
                    </b-button>
                </td>
            </tr>
        </table>
    </div>
</template>

<script>
import Services from "../../../scripts/services/services";
import VInput from "../controls/VInput/VInput.vue";
import DatePicker from "vue2-datepicker";
import {mapActions} from "vuex";

export default {
    name: 'billing-report',
    props: {
        model: Object,
        type: {
            type: String,
            required: true,
        },
        title: String,
        rightsBlock: Number,
    },
    components: {
        VInput,
        DatePicker
    },
    data() {
        return {
            form: {
                billing_cycle: null,
            },
            monthly: true,
            billing: {
                period: null,
            },
            billingList: {},
            billingReports: {},
            newReportDates: {
                dateFrom:null,
                dateTo:null,
            },
            billingOperation: {},
        }
    },
    computed: {
        statuses() {
            let billingReportStatuses = this.billingReportStatus;
            let statuses = {};
            statuses[billingReportStatuses.new] = {text:'модерация', badge:'light'};
            statuses[billingReportStatuses.viewed] = {text:'просмотрен', badge:'primary'};
            statuses[billingReportStatuses.accepted] = {text:'подтвержден', badge:'success'};
            statuses[billingReportStatuses.rejected] = {text:'отклонен', badge:'danger'};
            statuses[billingReportStatuses.waiting] = {text:'отправлен', badge:'warning'};
            statuses[billingReportStatuses.payed] = {text:'оплачен', badge:'success'};

            return statuses;
        }
    },
    methods: {
        ...mapActions({
            showMessageBox: 'modal/showMessageBox',
        }),
        setMonthlyPeriod() {
            if (this.monthly && this.form.billing_cycle && this.form.billing_cycle !== 0) {

                this.form.billing_cycle = 0;
                this.saveBillingCycle();
            }
        },
        saveBillingCycle() {
            Services.showLoader();
            Services.net().put(this.getRoute('billingReport.detail.billing_cycle',
                    {entityId: this.model.id}),
                {
                    billing_cycle: this.form.billing_cycle
                }).then(data => {
                if (!data) {
                    Services.msg('Изменения успешно сохранены')
                }
            }, () => {
                Services.msg('Не удалось сохранить изменения', 'danger')
            }).finally(() => {
                Services.hideLoader();
            })
        },
        isPeriod(period) {
            if (!period || period.length !== 2) {
                return false;
            }
            this.newReportDates = {
                dateFrom: period[0],
                dateTo: period[1],
            }
            return period[0] && period[1];
        },
        createReport() {
            if (!this.newReportDates) { return false; }
            Services.showLoader();
            Services.net()
                .post(this.getRoute('billingReport.detail.create', {type: this.type, entityId: this.model.id}), {},
                    { date_from: this.newReportDates.dateFrom,  date_to: this.newReportDates.dateTo})
                .then(() => {
                    this.loadReports();
                    this.newReportDates = {dateFrom: null, dateTo: null};
                    this.billing.period = null;
                })
                .catch(() => {
                    this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                }).finally(() => {
                Services.hideLoader();
                this.showMessageBox({title: 'Отчет создан'});
            });
        },
        removeItem(id) {
            Services.showLoader();
            Services.net()
                .delete(this.getRoute('billingReport.detail.delete', {entityId: this.model.id, reportId: id, type: this.type}))
                .then(() => {
                    this.loadReports();
                })
                .catch(() => {
                    this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                }).finally(() => {
                Services.hideLoader();
                this.showMessageBox({title: 'Отчет удалён'});
            });
        },
        updateStatus(id, status) {
            Services.showLoader();
            Services.net()
                .put(
                    this.getRoute('billingReport.detail.updateStatus', {entityId: this.model.id, reportId: id, type: this.type}),
                    {},
                    {status: status}
                )
                .then(() => {
                    this.loadReports();
                })
                .catch(() => {
                    this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                }).finally(() => {
                Services.hideLoader();
                this.showMessageBox({title: 'Статус Обновлен'});
            });
        },
        loadReports() {
            Services.showLoader();
            Services.net().get(this.getRoute('billingReport.detail.reports', {entityId: this.model.id, type: this.type}))
                .then(data => {
                    this.billingReports = data.billing_reports;
                }).finally(() => {
                Services.hideLoader();
            });
        },
        checkRights() {
            return this.canUpdate(this.rightsBlock);
        },
        getEntityId() {
            return this.model.id;
        },
        getStatus(id) {
            return this.statuses[id].text;
        },
        getBadge(id) {
            return this.statuses[id].badge;
        },
        getType() {
            return this.type;
        },
    },
    created() {
        Services.net().get(this.getRoute('billingReport.detail.billing', {entityId: this.model.id, type: this.type}))
            .then(data => {
                if (data.billing_cycle) {
                    this.form.billing_cycle = data.billing_cycle;
                    this.monthly = false;
                }
            })
            .finally(() => {
                Services.hideLoader();
            });
        this.loadReports();
    },
    watch: {
        monthly: function () {
            this.setMonthlyPeriod();
        }
    }
}
</script>