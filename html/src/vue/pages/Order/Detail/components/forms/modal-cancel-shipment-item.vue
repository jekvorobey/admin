<template>
    <b-modal id="modal-cancel-shipment-item" hide-footer ref="modal">
        <template v-slot:modal-title>
            Количество к отмене
        </template>
        <template v-slot:default="{close}">
            <b-form-row>
                <b-col>
                    <v-input v-model="$v.form['qty'].$model" :error="errorQty()">
                        <img :src="productPhoto(basketItem.product)" class="preview" :alt="basketItem.name" v-if="basketItem.product.mainImage">
                        {{ basketItem.name }}
                        <small>{{ basketItem.product.vendor_code }}</small>
                    </v-input>
                    <v-select
                        v-model="$v.returnReason.$model"
                        :options="orderReturnReasonsOptions"
                        :nullable-value="0"
                    >
                        Причина отмены*
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
    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';

    import {validationMixin} from 'vuelidate';
    import {between, integer, required} from 'vuelidate/lib/validators';

    export default {
        name: 'modal-cancel-shipment-item',
        components: {
            VSelect,
            VInput,
        },
        props: [
            'modelShipment',
            'modelOrder',
            'basketItem',
            'maxQty',
            'returnReasons',
        ],
        mixins: [
            validationMixin,
        ],
        data() {
            return {
                form: {
                    'qty': parseInt(this.basketItem.qty),
                    'returnReason': this.returnReasons.returnReason ? this.returnReasons.returnReason.text : 0,
                }
            };
        },
        validations() {
            return {
                form: {
                    'qty': {
                        integer,
                        between: between(1, this.maxQty)
                    },
                    'returnReason': {required},
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
                    this.getRoute('orders.detail.shipments.cancelShipmentItem', {
                        id: this.order.id,
                        shipmentId: this.shipment.id,
                        basketItemId: this.basketItem.id,
                    }),
                    {},
                    {qty: this.form['qty'], return_reason_id: this.form['returnReason']})
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
            orderReturnReasonsOptions() {
                return Object.values(this.returnReasons).map(returnReason => ({
                    value: returnReason.id,
                    text: returnReason.text
                }));
            },
        },
        created() {
            setTimeout(() => this.$bvModal.show('modal-cancel-shipment-item'), 100);
        }
};
</script>

<style>
    .preview {
        height: 50px;
        border-radius: 5px;
    }
</style>