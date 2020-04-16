<template>
    <table class="table">
        <thead>
        <tr>
            <th>
                Служба доставки
            </th>
            <th>PCT (мин) <fa-icon icon="question-circle" v-b-popover.hover="helpPct"></fa-icon></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="deliveryKpiPct in deliveryKpiPcts">
            <td>
                {{deliveryKpiPct.delivery_service.name}}
            </td>
            <td>
                <v-input type="number" v-model.number="$v.form[deliveryKpiPct.delivery_service_id].pct.$model" :error="errorPct(deliveryKpiPct.delivery_service_id)"></v-input>
            </td>
            <td>
                <button class="btn btn-success btn-sm" @click="save(deliveryKpiPct.delivery_service_id)" :disabled="!$v.form[deliveryKpiPct.delivery_service_id].$anyDirty" title="Сохранить">
                    <fa-icon icon="save"/>
                </button>
            </td>
        </tr>
        <tr v-if="deliveryServiceOptions.length > 0">
            <td>
                <v-select v-model="$v.form['new'].delivery_service_id.$model" :options="deliveryServiceOptions" :error="errorDeliveryServiceId('new')"></v-select>
            </td>
            <td>
                <v-input type="number" v-model.number="$v.form['new'].pct.$model" :error="errorPct('new')"></v-input>
            </td>
            <td>
                <button class="btn btn-success btn-sm" @click="save('new')" :disabled="!$v.form['new'].$anyDirty" title="Сохранить">
                    <fa-icon icon="plus"/>
                </button>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';

    import {integer, minValue, required} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    export default {
        name: 'tab-pct',
        components: {
            VInput,
            VSelect,
        },
        mixins: [
            validationMixin,
        ],
        data() {
            return {
                deliveryKpiPcts: [],
                deliveryServices: [],
                form: {
                    new: {
                        delivery_service_id: 0,
                        pct: 0,
                    }
                },
            }
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('deliveryKpi.pct.get')).then(data => {
                this.deliveryKpiPcts = data.deliveryKpiPcts;
                this.deliveryServices = data.deliveryServices;
                this.fillForm();
            }).finally(() => {
                Services.hideLoader();
            });
        },
        validations() {
            let form = {};
            for (let [id, deliveryKpiPct] of Object.entries(this.deliveryKpiPcts)) {
                form[deliveryKpiPct.delivery_service_id] = {
                    pct: {
                        required,
                        integer,
                        minValue: minValue(0),
                    },
                };
            }
            form['new'] = {
                delivery_service_id: {
                    required,
                    integer,
                    minValue: minValue(1),
                },
                pct: {
                    required,
                    integer,
                    minValue: minValue(0),
                },
            };

            return {form};
        },
        methods: {
            fillForm() {
                for (let [id, deliveryKpiPct] of Object.entries(this.deliveryKpiPcts)) {
                    this.$set(this.form, deliveryKpiPct.delivery_service_id, {
                        delivery_service_id: deliveryKpiPct.delivery_service_id,
                        pct: deliveryKpiPct.pct,
                    });
                    this.$v.form[deliveryKpiPct.delivery_service_id].$reset();
                }
                this.$set(this.form, 'new', {
                    delivery_service_id: 0,
                    pct: 0,
                });
                this.$v.form['new'].$reset();
            },
            save(id) {
                this.$v.form[id].$touch();
                if (this.$v.form[id].$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('deliveryKpi.pct.set'), {}, this.form[id]).then(data => {
                    this.deliveryKpiPcts = data.deliveryKpiPcts;
                    this.deliveryServices = data.deliveryServices;
                    this.fillForm();

                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            errorDeliveryServiceId(id) {
                if (this.$v.form.hasOwnProperty(id) && this.$v.form[id].delivery_service_id.$dirty) {
                    if (!this.$v.form[id].delivery_service_id.minValue) {
                        return "Обязательное поле!";
                    }
                }
            },
            errorPct(id) {
                if (this.$v.form.hasOwnProperty(id) && this.$v.form[id].pct.$dirty) {
                    if (!this.$v.form[id].pct.required) {
                        return "Обязательное поле!";
                    }
                    if (!this.$v.form[id].pct.integer || !this.$v.form[id].pct.minValue) {
                        return "Только целое число больше, либо равно 0";
                    }
                }
            },
        },
        computed: {
            deliveryServiceOptions() {
                return Object.values(this.deliveryServices).map(deliveryService => ({value: deliveryService.id, text: deliveryService.name}));
            },
            helpPct() {
                return 'Planned Сonsolidation Time - плановое время доставки заказа от склада мерчанта до логистического хаба ЛО и обработки заказа в сортировочном центре или хабе на стороне ЛО';
            },
        },
    };
</script>
