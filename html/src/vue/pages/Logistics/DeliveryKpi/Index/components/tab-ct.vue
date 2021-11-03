<template>
    <table class="table">
        <thead>
        <tr>
            <th>
                Мерчант
            </th>
            <th>CT (мин) <fa-icon icon="question-circle" v-b-popover.hover="helpCt"></fa-icon></th>
            <th v-if="canUpdate(blocks.logistics)"></th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="deliveryKpiCt in deliveryKpiCts">
            <td>
                {{deliveryKpiCt.merchant.legal_name}}
            </td>
            <td>
                <v-input type="number" v-model.number="$v.form[deliveryKpiCt.merchant_id].ct.$model" :error="errorCt(deliveryKpiCt.merchant_id)"></v-input>
            </td>
            <td v-if="canUpdate(blocks.logistics)">
                <button class="btn btn-success btn-sm" @click="save(deliveryKpiCt.merchant_id)" :disabled="!$v.form[deliveryKpiCt.merchant_id].$anyDirty" title="Сохранить">
                    <fa-icon icon="save"/>
                </button>
            </td>
        </tr>
        <tr v-if="merchantOptions.length > 0 && canUpdate(blocks.logistics)">
            <td>
                <v-select v-model="$v.form['new'].merchant_id.$model" :options="merchantOptions" :error="errorMerchantId('new')"></v-select>
            </td>
            <td>
                <v-input type="number" v-model.number="$v.form['new'].ct.$model" :error="errorCt('new')"></v-input>
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
        name: 'tab-ct',
        components: {
            VInput,
            VSelect,
        },
        mixins: [
            validationMixin,
        ],
        data() {
            return {
                deliveryKpiCts: [],
                merchants: [],
                form: {
                    new: {
                        merchant_id: 0,
                        ct: 0,
                    }
                },
            }
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('deliveryKpi.ct.get')).then(data => {
                this.deliveryKpiCts = data.deliveryKpiCts;
                this.merchants = data.merchants;
                this.fillForm();
            }).finally(() => {
                Services.hideLoader();
            });
        },
        validations() {
            let form = {};
            for (let [id, deliveryKpiCt] of Object.entries(this.deliveryKpiCts)) {
                form[deliveryKpiCt.merchant_id] = {
                    ct: {
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
                ct: {
                    required,
                    integer,
                    minValue: minValue(0),
                },
            };

            return {form};
        },
        methods: {
            fillForm() {
                for (let [id, deliveryKpiCt] of Object.entries(this.deliveryKpiCts)) {
                    this.$set(this.form, deliveryKpiCt.merchant_id, {
                        merchant_id: deliveryKpiCt.merchant_id,
                        ct: deliveryKpiCt.ct,
                    });
                    this.$v.form[deliveryKpiCt.merchant_id].$reset();
                }
                this.$set(this.form, 'new', {
                    merchant_id: 0,
                    ct: 0,
                });
                this.$v.form['new'].$reset();
            },
            save(id) {
                this.$v.form[id].$touch();
                if (this.$v.form[id].$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('deliveryKpi.ct.set'), {}, this.form[id]).then(data => {
                    this.deliveryKpiCts = data.deliveryKpiCts;
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
            errorCt(id) {
                if (this.$v.form.hasOwnProperty(id) && this.$v.form[id].ct.$dirty) {
                    if (!this.$v.form[id].ct.required) {
                        return "Обязательное поле!";
                    }
                    if (!this.$v.form[id].ct.integer || !this.$v.form[id].ct.minValue) {
                        return "Только целое число больше, либо равно 0";
                    }
                }
            },
        },
        computed: {
            merchantOptions() {
                return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.legal_name}));
            },
            helpCt() {
                return 'Confirmation Time - время перехода Отправления из статуса “Ожидает подтверждения” в статус “На комплектации”';
            },
        },
    };
</script>
