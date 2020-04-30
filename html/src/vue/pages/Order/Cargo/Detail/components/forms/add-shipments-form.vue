<template>
    <div>
        <template v-if="Object.values(shipments).length">
            <div v-for="shipment in shipments">
                <input :id="'shipment_' + shipment.id" type="checkbox" v-model="form.shipment_id" :value="shipment.id"/>
                <label :for="'shipment_' + shipment.id">{{shipment.number}}</label>
            </div>
            <button @click="save" class="btn btn-dark mt-3" :disabled="!form.shipment_id.length">Сохранить</button>
        </template>
        <template v-else>
            <span v-if="isLoading">Подождите, идёт загрузка...</span>
            <span v-else>Нет собранных неотгруженных заказов</span>
        </template>
    </div>
</template>

<script>
    import {validationMixin} from 'vuelidate';

    import Services from '../../../../../../../scripts/services/services';

    import VInput from '../../../../../../components/controls/VInput/VInput.vue';

    export default {
        components: {
            VInput,
        },
        mixins: [validationMixin],
        props: [
            'cargo',
        ],
        created() {
            this.loadUnshippedShipments();
        },
        data () {
            return {
                isLoading: false,
                shipments: {},
                form: {
                    shipment_id: []
                }
            };
        },
        methods: {
            loadUnshippedShipments() {
                this.isLoading = true;
                Services.net().get(this.getRoute('cargo.unshippedShipments', {id: this.cargo.id})).then(result => {
                    this.shipments = result.shipments;
                    this.isLoading = false;
                });
            },
            save() {
                Services.net().post(this.getRoute('cargo.addShipment2Cargo', {id: this.cargo.id}), {}, {
                    shipment_id: this.form.shipment_id,
                })
                    .then(result => {
                        this.$emit('onSave', result);
                    });
            },
        },
    }
</script>