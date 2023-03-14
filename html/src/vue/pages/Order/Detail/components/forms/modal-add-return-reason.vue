<template>
    <b-modal :id="'modal-add-return-reason-' + type" hide-footer ref="modal" size="lg" @hidden="resetModal">
        <template v-slot:default="{close}">
            <b-form-row>
                <div class="col-sm-4">
                    <v-select
                        v-model="$v.returnReason.$model"
                        :options="orderReturnReasonsOptions"
                        :nullable-value="0"
                    >
                        Причина отмены*
                    </v-select>
                </div>
            </b-form-row>
            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="save" :disabled="!$v.returnReason.$anyDirty">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';

import {required} from 'vuelidate/lib/validators';
import {validationMixin} from "vuelidate";

export default {
    name: 'modal-add-return-reason',
    components: {
        VSelect,
    },
    props: [
        'order',
        'returnReasons',
        'type'
    ],
    mixins: [
        validationMixin,
    ],
    data() {
        return {
            returnReason: this.returnReasons.returnReason ? this.returnReasons.returnReason.text : 0,
        }
    },
    validations() {
        return {
            returnReason: {required}
        };
    },
    methods: {
        createWarrningMessages() {
          const shipmentStatuses = [0,1,2,3,4];
          let message = 'Вы уверены что хотите отменить заказ? \n';

          if(this.order.payment_status.id === 2) message += 'Внимание, заказ оплачен, будет проведен возврат средств \n\n';
          if(this.order.payment_method_id === 3 || this.order.payment_method_id === 7) message += 'Внимание, отмена кредитной оплаты это геморой, согласуйте с Финансами и Колл-Центром \n\n';

          const isShipment = this.order.shipments.filter(shipment => !shipmentStatuses.includes(shipment.status.id));
          if(this.order.shipments > 0 && isShipment > 0)  message += '"Внимание! Есть отправления, которые уже возможно переданы курьеру. Ты уверен что хочешь отменить? Согласовать с Логистикой \n';

          return message;
        },
        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }
            if (window.confirm(this.createWarrningMessages())) {
              this.$emit('update:modelElement', this.returnReason)
              this.$bvModal.hide('modal-add-return-reason-' + this.type);
            }
        },
        resetModal() {
            this.returnReason = 0;
        },
    },
    computed: {
        orderReturnReasonsOptions() {
            return Object.values(this.returnReasons).map(returnReason => ({
                value: returnReason.id,
                text: returnReason.text
            }));
        },
    },
    created() {
        this.resetModal();
    }
}
</script>
