<template>
    <b-modal :id="'modal-credit-payment-receipt-create'" title="Сформирование фискального чека" hide-footer ref="modal" size="md" @hidden="resetModal">
        <template v-slot:default="{close}">
            <b-form-row>
                <div class="col-12 text-center">
                    <button class="btn btn-info mb-1 w-75" @click="createCreditPaymentReceipt(1)">Расчёт "ПРЕДОПЛАТА"</button>
                    <p class="text-left small">Формирование кассового чека с расчетом "Предоплата", если заказ в статусе "Передан в доставку", статус кредитной заявки сменился на "Cached" и нет ранее сформированных чеков</p>
                    <br>
                    <button class="btn btn-info mb-1 w-75" @click="createCreditPaymentReceipt(2)">Расчёт "В КРЕДИТ"</button>
                    <p class="text-left small">Формирование кассового чека с расчетом "В кредит", если заказ в статусе "Передан в доставку" и нет ранее сформированных чеков</p>
                    <br>
                    <button class="btn btn-info mb-1 w-75" @click="createCreditPaymentReceipt(3)">Расчёт "ПОГАШЕНИЕ КРЕДИТА"</button>
                    <p class="text-left small">Формирование кассового чека с расчетом "Погашение кредита", если статус кредитной заявки сменился на "Cached", заказ еще в пути и ранее был выбит чек с расчетом "В кредит"</p>
                    </div>
            </b-form-row>
            <div class="float-right mt-3">
                <button class="btn btn-info" @click="paymentCheckCreditStatus()">Проверить статус кредитной заявки</button>
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import {validationMixin} from "vuelidate";

    export default {
        name: 'modal-credit-payment-receipt-create',
        props: [
            'order'
        ],
        mixins: [
            validationMixin,
        ],
        validations() {
            return true;
        },
        methods: {
            paymentCheckCreditStatus() {
                this.$emit('update:paymentCheckCreditStatus');
            },
            createCreditPaymentReceipt(receiptType) {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                this.$emit('update:createCreditPaymentReceipt', receiptType);
                this.$bvModal.hide('modal-credit-payment-receipt-create');
            },
            resetModal() {
            },
        },
        computed: {
        },
        created() {
            this.resetModal();
        }
    }
</script>
