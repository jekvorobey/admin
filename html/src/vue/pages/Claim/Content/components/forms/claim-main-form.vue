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
                    <div class="mb-3">
                        <input type="text"
                               v-on="inputListeners"
                               v-model="productIdsList"
                               class="form-control"
                               :class="{ 'is-invalid': errorProductIdsFields() || errorInvalidProductId() }"
                               :disabled="!productIdsSelect || processing || availableIds.length < 1"
                        />
                        <small class="form-text text-muted">ID товаров через запятую</small>
                        <span class="invalid-feedback" role="alert">
                            {{ errorInvalidProductId() }}
                        </span>
                    </div>
                    <div v-for="(v, index) in productIdsSelect" class="input-group mb-3">
                        <b-form-select v-model="productIdsSelect[index]"
                                       :options="availableIds"
                                       :class="{ 'is-invalid': errorProductIdsFields() }"
                                       :disabled="!productIdsSelect || processing || availableIds.length < 1"
                        ></b-form-select>
                        <div class="input-group-append">
                            <button @click="deleteField(index)"
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    :disabled="productIdsSelect.length === 1">
                                <fa-icon icon="trash-alt"></fa-icon>
                            </button>
                        </div>
                        <span class="invalid-feedback" role="alert">
                            {{ errorProductIdsFields() }}
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
    import inputMixin from '../../../../../mixins/input-mixin';
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
                productIdsList: '',
                productIdsSelect: [''],
                productIdsOverall: [],
                form: {
                    'merchant_id': null,
                    'type': null,
                    'unpacking': null
                },
                availableIds: [],
                processing: false
            }
        },
        mixins: [validationMixin, inputMixin],
        validations() {
            return {
                form: {
                    merchant_id: {
                        required
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
                },
                productIdsOverall: {
                    required
                },
            }
        },
        methods: {
            getAvailableIds() {
                this.productIdsList = '';
                this.productIdsSelect = [''];
                this.$v.productIdsOverall.$reset();

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
                this.productIdsSelect.push('');
            },
            deleteField(index) {
                this.productIdsSelect.splice(index, 1);
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
            errorProductIdsFields() {
                if (this.$v.productIdsOverall.$dirty && this.$v.productIdsOverall.$invalid) {
                    return "Введите ID товаров в поле или выберите из выпадающего меню";
                }
            },
            errorInvalidProductId() {
                if (this.productIdsFromList.some((id) => {
                    return !this.availableIds.includes(id);
                })) {
                    return "Введенного ID нет в списке";
                }
            },
            getFilteredData() {
                let data = Object.assign({}, this.form, {product_ids: this.$v.productIdsOverall.$model});
                if (this.noUnpack.includes(parseInt(data.type))) {
                    data.unpacking = null;
                }
                return data;
            },
            formatIds(ids) {
                if (!ids) {
                    return [];
                }
                return ids
                    .split(',')
                    .map(id => { return parseInt(id); })
                    .filter(id => { return id > 0 });
            },
            setProductIds() {
                let productIdsList = this.productIdsFromList;
                let productIdsSelect = this.productIdsSelect.filter((id) => {
                    return id > 0;
                });
                this.$v.productIdsOverall.$model = productIdsList
                    .concat(productIdsSelect)
                    .filter((v, i, a) => {
                        return a.indexOf(v) === i;
                    });
            },
            save() {
                this.setProductIds();
                this.$v.$touch();
                if (this.$v.productIdsOverall.$invalid || this.errorInvalidProductId() || this.$v.form.$invalid) {
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
            productIdsFromList() {
                return (this.productIdsList.match(/\d+/g) || []).map((id) => {
                    return parseInt(id);
                });
            },
        },
        watch: {
            'productIdsList': {
                handler(val, oldVal) {
                    this.$v.productIdsOverall.$reset();
                    if (val && val !== oldVal) {
                        let format = this.formatIds(this.productIdsList).join(', ');
                        let separator = val.slice(-1) === ','
                            ? ','
                            : (val.slice(-2) === ', ' ? ', ' : '');
                        this.productIdsList = format + separator;
                    }
                },
            },
            'productIdsSelect': {
                handler(val, oldVal) {
                    this.$v.productIdsOverall.$reset();
                }
            }
        }
    }
</script>

<style scoped>
    .prop-name {
        width: 57%;
    }
</style>