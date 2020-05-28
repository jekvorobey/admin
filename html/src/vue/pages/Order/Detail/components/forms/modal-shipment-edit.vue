<template>
    <b-modal id="modal-shipment-edit" hide-footer ref="modal" size="lg" @hidden="resetModal">
        <template v-slot:modal-title>
            Редактирование отправления {{shipment.number}}
        </template>
        <template v-slot:default="{close}">
            <b-form-row>
                <div class="col-sm-4">
                    <v-select v-model="$v.form.status.$model" :options="shipmentStatusOptions">
                        Статус отправления
                    </v-select>
                </div>
                <div class="col-sm-4">
                    <v-select v-model="$v.form.delivery_service_zero_mile.$model" :options="deliveryServiceOptions">
                        Логистический оператор для нулевой мили
                    </v-select>
                </div>
                <div class="col-sm-4">
                    <v-date v-model="$v.form.psd.$model" :error="errorPdd">
                        PSD
                    </v-date>
                </div>
            </b-form-row>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="save">Сохранить</button>
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
                shipmentStatuses: {},
                deliveryServices: {},

                form: {
                    status: this.modelShipment.status.id,
                    delivery_service_zero_mile: this.modelShipment.tariff_id,
                    psd: this.modelShipment.pdd_original,
                },
            }
        },
        validations() {
            const notRequired = {required: requiredIf(() => {return false;})};

            return {
                form: {
                    status: {required},
                    delivery_service_zero_mile: {notRequired},
                    psd: {required},
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

                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            cancel() {
                this.form.status = this.modelShipment.status;
                this.form.delivery_service_zero_mile = this.modelShipment.delivery_service_zero_mile;
                this.form.psd = this.modelShipment.psd;
                this.$v.$reset();
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
                return Object.values(this.shipmentStatuses).map(deliveryStatus => ({
                    value: shipmentStatus.id,
                    text: shipmentStatus.name
                }));
            },
            deliveryServiceOptions() {
                return Object.values(this.deliveryServicees).map(deliveryService => ({
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
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('orders.detail.shipments', {id: this.order.id, shipmentId: this.shipment.id})).then(data => {
                this.shipmentStatuses = data.shipmentStatuses;
                this.deliveryServices = data.deliveryServices;
                this.$bvModal.show('modal-shipment-edit');
            }).finally(() => {
                Services.hideLoader();
            });
        }
};
</script>