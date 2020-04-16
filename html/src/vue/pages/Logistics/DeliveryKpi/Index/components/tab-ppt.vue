<template>
    <table class="table">
        <thead>
        <tr>
            <th>
                Мерчант
            </th>
            <th>PPT (мин) <fa-icon icon="question-circle" v-b-popover.hover="helpPpt"></fa-icon></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="deliveryKpiPpt in deliveryKpiPpts">
            <td>
                {{deliveryKpiPpt.merchant.legal_name}}
            </td>
            <td>
                <v-input type="number" v-model.number="$v.form[deliveryKpiPpt.merchant_id].ppt.$model" :error="errorPpt(deliveryKpiPpt.merchant_id)"></v-input>
            </td>
            <td>
                <button class="btn btn-success btn-sm" @click="save(deliveryKpiPpt.merchant_id)" :disabled="!$v.form[deliveryKpiPpt.merchant_id].$anyDirty" title="Сохранить">
                    <fa-icon icon="save"/>
                </button>
            </td>
        </tr>
        <tr v-if="merchantOptions.length > 0">
            <td>
                <v-select v-model="$v.form['new'].merchant_id.$model" :options="merchantOptions" :error="errorMerchantId('new')"></v-select>
            </td>
            <td>
                <v-input type="number" v-model.number="$v.form['new'].ppt.$model" :error="errorPpt('new')"></v-input>
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
        name: 'tab-ppt',
        components: {
            VInput,
            VSelect,
        },
        mixins: [
            validationMixin,
        ],
        data() {
            return {
                deliveryKpiPpts: [],
                merchants: [],
                form: {
                    new: {
                        merchant_id: 0,
                        ppt: 0,
                    }
                },
            }
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('deliveryKpi.ppt.get')).then(data => {
                this.deliveryKpiPpts = data.deliveryKpiPpts;
                this.merchants = data.merchants;
                this.fillForm();
            }).finally(() => {
                Services.hideLoader();
            });
        },
        validations() {
            let form = {};
            for (let [id, deliveryKpiPpt] of Object.entries(this.deliveryKpiPpts)) {
                form[deliveryKpiPpt.merchant_id] = {
                    ppt: {
                        required,
                        integer,
                        minValue: minValue(0),
                    },
                };
            }
            form['new'] = {
                merchant_id: {
                    required,
                    integer,
                    minValue: minValue(1),
                },
                ppt: {
                    required,
                    integer,
                    minValue: minValue(0),
                },
            };

            return {form};
        },
        methods: {
            fillForm() {
                for (let [id, deliveryKpiPpt] of Object.entries(this.deliveryKpiPpts)) {
                    this.$set(this.form, deliveryKpiPpt.merchant_id, {
                        merchant_id: deliveryKpiPpt.merchant_id,
                        ppt: deliveryKpiPpt.ppt,
                    });
                    this.$v.form[deliveryKpiPpt.merchant_id].$reset();
                }
                this.$set(this.form, 'new', {
                    merchant_id: 0,
                    ppt: 0,
                });
                this.$v.form['new'].$reset();
            },
            save(id) {
                this.$v.form[id].$touch();
                if (this.$v.form[id].$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('deliveryKpi.ppt.set'), {}, this.form[id]).then(data => {
                    this.deliveryKpiPpts = data.deliveryKpiPpts;
                    this.merchants = data.merchants;
                    this.fillForm();

                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            errorMerchantId(id) {
                if (this.$v.form.hasOwnProperty(id) && this.$v.form[id].merchant_id.$dirty) {
                    if (!this.$v.form[id].merchant_id.minValue) {
                        return "Обязательное поле!";
                    }
                }
            },
            errorPpt(id) {
                if (this.$v.form.hasOwnProperty(id) && this.$v.form[id].ppt.$dirty) {
                    if (!this.$v.form[id].ppt.required) {
                        return "Обязательное поле!";
                    }
                    if (!this.$v.form[id].ppt.integer || !this.$v.form[id].ppt.minValue) {
                        return "Только целое число больше, либо равно 0";
                    }
                }
            },
        },
        computed: {
            merchantOptions() {
                return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.legal_name}));
            },
            helpPpt() {
                return 'Planned Processing Time - плановое время для прохождения Отправлением статусов от “На комплектации” до “Готов к передаче ЛО”';
            },
        },
    };
</script>
