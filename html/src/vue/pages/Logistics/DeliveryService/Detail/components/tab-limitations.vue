<template>
    <div>
        <div class="row">
            <div class="col">
                <div class="float-right">
                    <button class="btn btn-success btn-sm" @click="save" :disabled="!$v.form.$anyDirty">
                        Сохранить
                    </button>
                    <button @click="cancel" class="btn btn-outline-danger btn-sm mr-1" :disabled="!$v.form.$anyDirty">
                        Отмена
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <v-select v-model="$v.form.do_dangerous_products_delivery.$model" :options="booleanOptions">
                    Доставка опасных грузов
                </v-select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <v-input type="number" v-model="$v.form.max_shipments_per_day.$model" :error="errorMaxShipmentsPerDay">
                    Максимальное кол-во отправленией в день
                </v-input>
            </div>
        </div>
    </div>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';

    import {integer, minValue, required} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    export default {
    name: 'tab-limitations',
    components: {
        VInput,
        VSelect,
    },
    props: [
        'model',
    ],
    mixins: [
        validationMixin,
    ],
    data() {
        return {
            form: {
                do_dangerous_products_delivery: this.model.do_dangerous_products_delivery,
                max_shipments_per_day: this.model.max_shipments_per_day,
            },
        }
    },
    validations: {
        form: {
            do_dangerous_products_delivery: {required},
            max_shipments_per_day: {integer, minValue:minValue(0)},
        }
    },
    methods: {
        save() {
            Services.showLoader();
            Services.net().put(this.getRoute('deliveryService.detail.limitations.save', {id: this.deliveryService.id}), {}, this.form).then(() => {
                this.deliveryService.do_dangerous_products_delivery = this.form.do_dangerous_products_delivery;
                this.deliveryService.max_shipments_per_day = this.form.max_shipments_per_day;

                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            });
        },
        cancel() {
            this.form.do_dangerous_products_delivery = this.deliveryService.do_dangerous_products_delivery;
            this.form.max_shipments_per_day = this.deliveryService.max_shipments_per_day;
        },
    },
    computed: {
        deliveryService: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        booleanOptions() {
            return [{value: 0, text: 'Нет'}, {value: 1, text: 'Да'}];
        },
        errorMaxShipmentsPerDay() {
            if (this.$v.form.max_shipments_per_day.$dirty) {
                if (!this.$v.form.max_shipments_per_day.integer) {
                    return "Только целые числа!";
                }
                if (!this.$v.form.max_shipments_per_day.minValue) {
                    return "Число должно быть больше, либо равно 0!";
                }
            }
        },
    },
};
</script>
