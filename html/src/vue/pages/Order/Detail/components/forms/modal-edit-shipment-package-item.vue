<template>
    <b-modal id="modal-edit-shipment-package-item" hide-footer ref="modal">
        <template v-slot:modal-title>
            Редактирование товара в коробке #{{shipmentPackageNum}} (ID: {{shipmentPackage.id}}, {{ shipmentPackage.package.name}}) для отправления {{shipment.number}}
        </template>
        <template v-slot:default="{close}">
            <b-form-row>
                <b-col>
                    <v-input v-model="$v.form['qty'].$model" :error="errorQty()">
                        <img :src="productPhoto(shipmentItem.basketItem.product)" class="preview" :alt="shipmentItem.basketItem.name" v-if="shipmentItem.basketItem.product.mainImage">
                        {{ shipmentItem.basketItem.name }}
                        <small>{{ shipmentItem.basketItem.product.vendor_code }}</small>
                    </v-input>
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
    import VInput from '../../../../../components/controls/VInput/VInput.vue';

    import {validationMixin} from 'vuelidate';
    import {between, integer} from 'vuelidate/lib/validators';

    export default {
        name: 'modal-edit-shipment-package-item',
        components: {
            VInput
        },
        props: [
            'modelShipment',
            'modelOrder',
            'shipmentPackage',
            'shipmentPackageNum',
            'shipmentItem',
            'maxQty',
        ],
        mixins: [
            validationMixin,
        ],
        data() {
            return {
                form: {
                    'qty': parseInt(this.shipmentItem.qty),
                }
            };
        },
        validations() {
            return {
                form: {
                    'qty': {
                        integer,
                        between: between(1, this.maxQty)
                    }
                }
            }
        },
        methods: {
            productPhoto(product) {
                return '/files/compressed/' + product.mainImage.file_id + '/50/50/webp';
            },
            errorQty() {
                if (this.$v.form['qty'].$dirty) {
                    if (this.$v.form['qty'].integer === false) {
                        return "Только целые числа!";
                    }
                    if (this.$v.form['qty'].between === false) {
                        return "Количество должно быть целым числом между 1 и " + this.maxQty + " включительно";
                    }
                }
            },
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(
                    this.getRoute('orders.detail.shipments.editShipmentPackageItem', {
                        id: this.order.id,
                        shipmentId: this.shipment.id,
                        shipmentPackageId: this.shipmentPackage.id,
                        basketItemId: this.shipmentItem.basketItem.id,
                    }),
                    {},
                    {qty: this.form['qty']})
                .then((data) => {
                    this.$set(this, 'order', data.order);
                    this.$set(this.order, 'shipments', data.order.shipments);
                    this.$emit('onSave');
                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                });
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
        },
        created() {
            setTimeout(() => this.$bvModal.show('modal-edit-shipment-package-item'), 100);
        }
};
</script>

<style>
    .preview {
        height: 50px;
        border-radius: 5px;
    }
</style>