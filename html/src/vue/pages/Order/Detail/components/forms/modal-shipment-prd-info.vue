<template>
    <b-modal :id="'modal-shipment-prd-info'" hide-footer ref="modal" size="lg" @hidden="resetModal">
        <template v-slot:default="{close}">
            <b-form-row>
                <div class="col-sm-4">
                    <v-input v-model="$v.form.payment_document_number.$model" :error="errorPDN">
                        Номер платежно-расчетного документа
                    </v-input>
                    <v-date v-model="$v.form.payment_document_date.$model" :error="errorPDD">
                        Дата платежно-расчетного документа
                    </v-date>
                </div>
            </b-form-row>
            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="save" :disabled="!$v.payment_document_number.$anyDirty">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
import VInput from '../../../../../components/controls/VInput/VInput.vue';
import VDate from '../../../../../components/controls/VDate/VDate.vue';

import {required} from 'vuelidate/lib/validators';
import {validationMixin} from "vuelidate";
import Services from "../../../../../../scripts/services/services";

export default {
    name: 'modal-shipment-prd-info',
    components: {
        VInput,
        VDate,
    },
    props: [
        'modelShipment',
        'modelOrder',
    ],
    mixins: [
        validationMixin,
    ],
    data() {
        return {
            form: {
                payment_document_number: this.modelShipment.payment_document_number,
                payment_document_date: this.modelShipment.payment_document_date,
            },
        }
    },
    validations() {
        return {
            form: {
                payment_document_number: {required},
                payment_document_date: {required},
            }
        };
    },
    methods: {
        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            Services.showLoader();
            Services.net().put(this.getRoute('orders.detail.shipments.savePrd', {id: this.order.id, shipmentId: this.shipment.id}), {}, this.form).then((data) => {
                this.$set(this, 'order', data.order);
                this.$set(this.order, 'shipments', data.order.shipments);
                this.$bvModal.hide('modal-shipment-prd-info');

                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            });
        },
        resetModal() {
            this.shipment = {};
        },
    },
    computed: {
        order: {
            get() {return this.modelOrder},
            set(value) {this.$emit('update:modelOrder', value)},
        },
        shipment: {
            get() {return this.modelShipment},
            set(value) {this.$emit('update:modelShipment', value)},
        },
        errorPDN() {
            if (this.$v.form.payment_document_number.$dirty) {
                if (!this.$v.form.payment_document_number.required) {
                    return "Обязательное поле";
                }
            }
        },
        errorPDD() {
            if (this.$v.form.payment_document_date.$dirty) {
                if (!this.$v.form.payment_document_date.required) {
                    return "Обязательное поле";
                }
            }
        },
    },
    created() {
        setTimeout(() => this.$bvModal.show('modal-shipment-prd-info'), 100);
    }
}
</script>
