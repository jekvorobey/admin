<template>
    <b-modal id="modal-shipment-edit" hide-footer ref="modal" size="lg" @hidden="resetModal">
        <template v-slot:modal-title>
            Редактирование отправления {{shipment.number}}
        </template>
        <template v-slot:default="{close}">
            <b-form-row>
                <div class="col-sm-6">
                    <v-select v-model="$v.form.status.$model" :options="shipmentStatusOptions">
                        Статус отправления*
                    </v-select>
                </div>
                <div class="col-sm-6">
                    <v-select v-model="$v.form.delivery_service_zero_mile.$model" :options="deliveryServiceOptions">
                        Логистический оператор для нулевой мили
                    </v-select>
                </div>
            </b-form-row>
            <b-form-row>
                <div class="col-sm-6">
                    <v-date v-model="$v.form.psd.$model" type="datetime-local" :error="errorPsd">
                        PSD*
                    </v-date>
                </div>
                <div class="col-sm-6" v-if="shipment.fsd">
                    <v-date v-model="$v.form.fsd.$model" :error="errorFsd">
                        FSD
                    </v-date>
                </div>
            </b-form-row>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="save" :disabled="!$v.form.$anyDirty">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import VDate from '../../../../../components/controls/VDate/VDate.vue';

    import {required, requiredIf} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    export default {
        name: 'modal-shipment-edit',
        components: {
            VDate,
            VInput,
            VSelect,
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
                    status: this.modelShipment.status ? this.modelShipment.status.id : 0,
                    delivery_service_zero_mile: this.modelShipment.tariff_id,
                    psd: this.modelShipment.psd_original,
                    fsd: this.modelShipment.fsd_original,
                },
            }
        },
        validations() {
            let self = this;
            const notRequired = {required: requiredIf(() => {return false;})};

            return {
                form: {
                    status: {required},
                    delivery_service_zero_mile: {notRequired},
                    psd: {required},
                    fsd: {required: requiredIf(() => {
                        return self.shipment.fsd;
                    })},
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
                Services.net().put(this.getRoute('orders.detail.shipments.save', {id: this.order.id, shipmentId: this.shipment.id}), {}, this.form).then((data) => {
                    this.order = data.order;
                    this.$bvModal.hide('modal-shipment-edit');

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
            shipmentStatusOptions() {
                return Object.values(this.shipmentStatuses).map(shipmentStatus => ({
                    value: shipmentStatus.id,
                    text: shipmentStatus.name
                }));
            },
            deliveryServiceOptions() {
                return Object.values(this.deliveryServices).map(deliveryService => ({
                    value: deliveryService.id,
                    text: deliveryService.name
                }));
            },
            errorPsd() {
                if (this.$v.form.psd.$dirty) {
                    if (!this.$v.form.psd.required) {
                        return "Обязательное поле";
                    }
                }
            },
            errorFsd() {
                if (this.$v.form.fsd.$dirty) {
                    if (!this.$v.form.fsd.required) {
                        return "Обязательное поле";
                    }
                }
            },
        },
        created() {
            setTimeout(() => this.$bvModal.show('modal-shipment-edit'), 100);
        }
};
</script>