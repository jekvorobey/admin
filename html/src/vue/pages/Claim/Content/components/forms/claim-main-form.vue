<template>
    <div>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td class="prop-name">
                    Мерчант
                </td>
                <td>
                    <div class="input-group mb-3">
                        <b-form-select @change="getAvailableIds()"
                                       v-model="$v.form.merchant_id.$model"
                                       :options="merchantOptions"
                                       :class="{ 'is-invalid': errorMerchantField() }"
                        ></b-form-select>
                        <span class="invalid-feedback" role="alert">
                            {{ errorMerchantField() }}
                        </span>
                    </div>
                </td>
            <tr>
                <td class="prop-name">
                    ID товаров
                    <button @click="addField()"
                            class="btn btn-outline-info float-right"
                            :disabled="!$v.form.merchant_id.$model|| processing || availableIds.length < 1">
                        <fa-icon icon="plus"></fa-icon>
                    </button>
                </td>
                <td class="prop-name">
                    <div v-for="(v, index) in $v.form.product_ids.$each.$iter" class="input-group mb-3">
                        <b-form-select v-model="$v.form.product_ids.$model[index]"
                                       :options="availableIds"
                                       :class="{ 'is-invalid': errorMultipleField(v) }"
                                       :disabled="!$v.form.merchant_id.$model|| processing || availableIds.length < 1"
                        ></b-form-select>
                        <div class="input-group-append">
                            <button @click="deleteField(index)"
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    :disabled="$v.form.product_ids.$model.length === 1">
                                <fa-icon icon="trash-alt"></fa-icon>
                            </button>
                        </div>
                        <span class="invalid-feedback" role="alert">
                            {{ errorMultipleField(v) }}
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="prop-name">
                    Тип производства контента
                </td>
                <td>
                    <div class="input-group mb-3">
                        <b-form-select v-model="$v.form.type.$model"
                                       :options="contentClaimOption"
                                       :class="{ 'is-invalid': errorField('type') }"
                        ></b-form-select>
                        <span class="invalid-feedback" role="alert">
                            {{ errorField('type') }}
                        </span>
                    </div>
                </td>
            </tr>
            <tr v-if="displayPhotoOptions">
                <td class="prop-name">
                    Тип фотосъёмки
                </td>
                <td>
                    <div class="input-group mb-3">
                        <b-form-select v-model="$v.form.unpacking.$model"
                                       :options="photoTypeOptions"
                                       :class="{ 'is-invalid': errorField('unpacking') }"
                        ></b-form-select>
                        <span class="invalid-feedback" role="alert">
                            {{ errorField('unpacking') }}
                        </span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <button @click="save()" class="btn btn-dark mt-3" :disabled="!$v.form.$anyDirty">Сохранить</button>
    </div>
</template>

<script>
    import { validationMixin } from 'vuelidate';
    import { required, requiredIf } from 'vuelidate/lib/validators';
    import Services from "../../../../../../scripts/services/services";

    export default {
        props: {
            merchantOptions: {},
            contentClaimOption: {},
            photoTypeOptions: {},
            noUnpack: Array
        },
        data () {
            return {
                form: {
                    'merchant_id': null,
                    'product_ids': [''],
                    'type': null,
                    'unpacking': null
                },
                availableIds: [],
                processing: false
            }
        },
        mixins: [validationMixin],
        validations() {
            return {
                form: {
                    merchant_id: {
                        required
                    },
                    product_ids: {
                        $each: {
                            required
                        }
                    },
                    type: {
                        required
                    },
                    unpacking: {
                        required: requiredIf(function () {
                            return this.$v.form.type.$model
                                && !this.noUnpack.includes(parseInt(this.$v.form.type.$model));
                        })
                     },
            }
        }
        },


        methods: {
            getAvailableIds() {
                this.$v.form.product_ids.$model = [''];
                this.$v.form.product_ids.$reset();

                let merchantId = this.$v.form.merchant_id.$model;
                if (!merchantId) return;

                this.processing = true;
                Services.net().get(this.getRoute('contentClaims.productsByMerchant'), {
                    'id': merchantId
                }).then(data => {
                    this.availableIds = data.ids;
                    this.processing = false;
                });
            },

            addField() {
                this.$v.form.product_ids.$model.push('');
            },

            deleteField(index) {
                this.$v.form.product_ids.$model.splice(index, 1);
            },

            errorMultipleField(item) {
                if (item.$dirty) {
                    if (!item.required) return "Выберите один из вариантов";
                }
            },

            errorField(propertyName) {
                if (this.$v.form[propertyName].$dirty) {
                    if (!this.$v.form[propertyName].required) return "Выберите один из вариантов";
                }
            },

            errorMerchantField() {
                if (this.$v.form.merchant_id.$model && !this.processing && this.availableIds.length < 1) {
                    return "У данного мерчанта нет товаров для выбора";
                }
                if (this.$v.form.merchant_id.$dirty) {
                    if (!this.$v.form.merchant_id.required) return "Выберите один из вариантов";
                }
            },

            getFilteredData() {
                let data = Object.assign({}, this.form);
                data.product_ids = data.product_ids.filter((v, i, a) => {
                    return a.indexOf(v) === i;
                });
                if (this.noUnpack.includes(parseInt(data.type))) {
                    data.unpacking = null;
                }
                return data;
            },

            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.net().post(this.getRoute('contentClaims.createClaim'), {}, this.getFilteredData())
                    .then(result => {
                        this.$emit('onSave', result);
                    });
            }
        },

        computed: {
            displayPhotoOptions() {
                this.$v.form.unpacking.$model = null;
                this.$v.form.unpacking.$reset();
                return this.$v.form.type.$model
                    && !this.noUnpack.includes(parseInt(this.$v.form.type.$model));
            },
        }
    }
</script>

<style scoped>
    .prop-name {
        width: 57%;
    }
</style>