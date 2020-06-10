<template>
    <b-modal id="modal-add-shipment-package-items" hide-footer ref="modal">
        <template v-slot:modal-title>
            Добавление товаров в коробку #{{shipmentPackageNum}} (ID: {{shipmentPackage.id}}, {{ shipmentPackage.package.name}}) для отправления {{shipment.number}}
        </template>
        <template v-slot:default="{close}">
            <b-form-row>
                <b-col>
                    <v-input v-for="basketItem in basketItems"
                             :key="basketItem.id" v-model="$v.form[basketItem.id].$model"
                             :error="errorQty(basketItem.id)">
                        <img :src="productPhoto(basketItem.product)" class="preview" :alt="basketItem.name"
                             v-if="basketItem.product.mainImage">
                        {{ basketItem.name }} <small>{{ basketItem.product ? basketItem.product.vendor_code : '' }}</small>
                    </v-input>
                </b-col>
            </b-form-row>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="save" :disabled="$v.form.$invalid">Сохранить</button>
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
        name: 'modal-add-shipment-package-items',
        components: {
            VInput
        },
        props: [
            'modelShipment',
            'modelOrder',
            'shipmentPackage',
            'shipmentPackageNum',
            'basketItems'
        ],
        mixins: [
            validationMixin,
        ],
        data() {
            let form = {};
            for (let [id, basketItem] of Object.entries(this.basketItems)) {
                form[id] = parseInt(basketItem.qty);
            }

            return {
                form
            };
        },
        validations() {
            let form = {};
            for (let [id, basketItem] of Object.entries(this.basketItems)) {
                form[id] = {
                    integer,
                    between: between(1, parseInt(basketItem.qty))
                };
            }

            return {form};
        },
        methods: {
            productPhoto(product) {
                return '/files/compressed/' + product.mainImage.file_id + '/50/50/webp';
            },
            errorQty(basketItemId) {
                if (this.$v.form[basketItemId].$dirty) {
                    if (this.$v.form[basketItemId].integer === false) {
                        return "Только целые числа!";
                    }
                    if (this.$v.form[basketItemId].between === false) {
                        return "Количество должно быть целым числом между 1 и " +
                            parseInt(this.basketItems[basketItemId].qty) + " включительно";
                    }
                }
            },
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().post(
                    this.getRoute('orders.detail.shipments.addShipmentPackageItems', {
                        id: this.order.id,
                        shipmentId: this.shipment.id,
                        shipmentPackageId: this.shipmentPackage.id
                    }),
                    {},
                    {basketItems: this.form})
                .then((data) => {
                    this.order = data.order;
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
            setTimeout(() => this.$bvModal.show('modal-add-shipment-package-items'), 100);
        }
};
</script>

<style>
    .preview {
        height: 50px;
        border-radius: 5px;
    }
</style>