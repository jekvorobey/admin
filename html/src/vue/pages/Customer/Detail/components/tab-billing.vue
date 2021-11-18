<template>
    <div>
        <billing-report
            :model.sync="model"
            :type="billingReportType.referral_partner"
            :title="'Отчеты реферального партнера'"
            :rightsBlock="blocks.referrals"
        ></billing-report>
        <table class="table">
        <thead>
            <tr>
                <th>ID заказа/транзакции</th>
                <th>Дата</th>
                <th>Комментарий</th>
                <th>Сумма</th>
                <th>Операция</th>
            </tr>
        </thead>
        <tbody>
            <tr class="table-info">
                <td colspan="3">Всего</td>
                <td>{{ roundValue(customer.referral_bill) }}</td>
                <td>
                    <button class="btn btn-sm btn-danger" v-b-modal="modalIdBillCorrect">Корректировка</button>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <select class="form-control form-control-sm" v-model="filter.type">
                        <option :value="null">-</option>
                        <option v-for="(type, id) in types" :value="id">{{ type }}</option>
                    </select>
                </td>
            </tr>
            <tr v-for="operation in filterOperations">
                <td>{{ operation.action_id }}</td>
                <td>{{ operation.created_at }}</td>
                <td>{{ operation.comment }}</td>
                <td>{{ roundValue(operation.value) }}</td>
                <td>{{ operation.type_name }}</td>
            </tr>
        </tbody>
        </table>
        <modal-bill-correct :id="modalIdBillCorrect" @save="saveOperation"/>
    </div>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import ModalBillCorrect from './modal-bill-correct.vue';
import Helpers from "../../../../../scripts/helpers.js";
import BillingReport from "../../../../components/billing-report/billing-report.vue";

export default {
    name: 'tab-billing',
    components: {
        ModalBillCorrect,
        BillingReport,
    },
    props: ['model'],
    data() {
        return {
            operations: [],
            types: {},
            filter: {
                type: null,
            },
            modalIdBillCorrect: 'modal-bill-correct',
        }
    },
    computed: {
        customer: {
            get() {return this.model;},
            set(value) {this.$emit('update:model', value);}
        },
        filterOperations() {
            return this.operations.filter(operation => {
                if (this.filter.type) {
                    if (operation.type !== Number(this.filter.type)) {
                        return false;
                    }
                }

                return true;
            });
        },
    },
    methods: {
        roundValue(value) {
            return Helpers.roundValue(value)
        },
        saveOperation({comment, value}) {
            Services.showLoader();
            Services.net().post(this.getRoute('customers.detail.billing.correct', {id: this.customer.id}), {
                comment: comment,
                value: value,
            }).then((data) => {
                this.operations = data.operations;
                this.customer.referral_bill = data.referral_bill;
                this.$bvModal.hide(this.modalIdBillCorrect);
            }).finally(() => {
                Services.hideLoader();
            })
        },
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.billing', {id: this.model.id})).then(data => {
            this.operations = data.operations;
            this.types = data.types;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>