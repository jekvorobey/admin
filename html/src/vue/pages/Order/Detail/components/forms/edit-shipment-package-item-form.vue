<template>
    <div>
        <v-input v-model="$v.form['qty'].$model" :error="errorQty()">
            {{ shipment.basketItems[basketItemId].name }}
            <small>{{ shipment.products[basketItemId].vendor_code }}</small>
        </v-input>
        <button @click="save" class="btn btn-dark mt-3" :disabled="!$v.form.$anyDirty">Сохранить</button>
    </div>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {between, integer} from 'vuelidate/lib/validators';

    import Services from '../../../../../../scripts/services/services';

    import VInput from '../../../../../components/controls/VInput/VInput.vue';

    export default {
        components: {
            VInput,
        },
        mixins: [validationMixin],
        props: {
            shipment: {},
            basketItemId: 0,
            shipmentPackageId: 0,
            qty: 0,
        },
        data () {
            return {
                form: {
                    'qty': this.qty,
                }
            };
        },
        validations() {
            return {
                form: {
                    'qty': {
                        integer,
                        between: between(0, this.qty)
                    }
                }
            }
        },
        methods: {
            errorQty() {
                if (this.$v.form['qty'].$dirty) {
                    if (this.$v.form['qty'].integer === false) {
                        return "Только целые числа!";
                    }
                    if (this.$v.form['qty'].between === false) {
                        return "Количество должно быть целым числом между 0 и " + this.qty;
                    }
                }
            },
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.net().put(
                    this.getRoute('shipment.editShipmentPackageItem', {
                        id: this.shipment.id,
                        shipmentPackageId: this.shipmentPackageId,
                        basketItemId: this.basketItemId,
                    }),
                    {},
                    {qty: this.form['qty']}
                )
                .then(result => {
                    this.$emit('onSave', result);
                });
            },
        },
    }
</script>
