<template>
    <div>
        <v-input v-for="basketItem in basketItems"
                :key="basketItem.id" v-model="$v.form[basketItem.id].$model"
                :error="errorQty(basketItem.id)">
            {{ basketItem.name }} <small>{{ product(basketItem.id).vendor_code }}</small>
        </v-input>
        <button @click="save" class="btn btn-dark mt-3">Сохранить</button>
    </div>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {integer, between} from 'vuelidate/lib/validators';

    import Services from "../../../../../../../scripts/services/services";
    import {mapGetters} from "vuex";

    import VInput from "../../../../../../components/controls/VInput/VInput.vue";

    export default {
        components: {
            VInput,
        },
        mixins: [validationMixin],
        props: {
            shipment: {},
            basketItems: {},
            shipmentPackageId: 0,
        },
        data () {
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
                    between: between(0, parseInt(this.basketItems[id].qty))
                };
            }

            return {form};
        },
        methods: {
            product(basketItemId) {
                return this.shipment.products[basketItemId];
            },
            errorQty(basketItemId) {
                if (this.$v.form[basketItemId].$dirty) {
                    if (this.$v.form[basketItemId].integer === false) {
                        return "Только целые числа!";
                    }
                    if (this.$v.form[basketItemId].between === false) {
                        return "Количество должно быть целым числом между 0 и " +
                            parseInt(this.basketItems[basketItemId].qty);
                    }
                }
            },
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.net().post(
                    this.getRoute('shipment.addShipmentPackageItems', {
                        id: this.shipment.id,
                        shipmentPackageId: this.shipmentPackageId
                    }),
                    {},
                    {basketItems: this.form})
                .then(result => {
                    this.$emit('onSave', result);
                });
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
        }
    }
</script>
