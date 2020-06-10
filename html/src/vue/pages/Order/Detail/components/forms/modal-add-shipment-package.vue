<template>
    <b-modal id="modal-add-shipment-package" hide-footer ref="modal" @hidden="resetModal">
        <template v-slot:modal-title>
            Добавление новой коробки для отправления {{shipment.number}}
        </template>
        <template v-slot:default="{close}">
            <b-form-row>
                <b-col>
                    <v-select v-model="$v.form.package_id.$model" :options="packageOptions">
                        Тип коробки
                    </v-select>
                </b-col>
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

    import {required} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    export default {
        name: 'modal-add-shipment-package',
        components: {
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
                packages: {},

                form: {
                    package_id: null,
                },
            }
        },
        validations: {
            form: {
                package_id: {required},
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().post(this.getRoute('orders.detail.shipments.addShipmentPackage', {id: this.order.id, shipmentId: this.shipment.id}), {}, this.form).then((data) => {
                    this.order = data.order;
                    this.$bvModal.hide('modal-add-shipment-package');

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
            packageOptions() {
                return Object.values(this.packages).map(item => ({value: item.id, text: item.name}));
            },
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('settings.packages.list')).then(data => {
                this.packages = data.packages;
                this.$bvModal.show('modal-add-shipment-package');
            }).finally(() => {
                Services.hideLoader();
            });
        }
};
</script>