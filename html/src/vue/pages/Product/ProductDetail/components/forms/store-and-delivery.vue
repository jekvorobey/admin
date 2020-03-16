<template>
    <div>
        <div class="row">
            <v-input v-model="$v.form.width.$model" :error="errorWidth" class="col-md-6 col-sm-12">
                Ширина, мм
            </v-input>
            <v-input v-model="$v.form.height.$model" :error="errorHeight" class="col-md-6 col-sm-12">
                Высота, мм
            </v-input>
            <v-input v-model="$v.form.length.$model" :error="errorLength" class="col-md-6 col-sm-12">
                Глубина, мм
            </v-input>
            <v-input v-model="$v.form.weight.$model" :error="errorWeight" class="col-md-6 col-sm-12">
                Вес, гр
            </v-input>
        </div>
        <hr>
        <div class="row">
            <v-input v-model="$v.form.need_special_case.$model" :error="errorSpecialCase" class="col-md-6 col-sm-12" placeholder="Нет">
                <input type="checkbox" v-model="checkSpecialCase"> Особая упаковка
            </v-input>
            <v-input v-model="$v.form.need_special_store.$model" :error="errorSpecialStore" class="col-md-6 col-sm-12" placeholder="Нет">
                <input type="checkbox" v-model="checkSpecialStore"> Особые условия хранения
            </v-input>
            <v-select v-model="$v.form.fragile.$model" :options="booleanOptions" class="col-md-6 col-sm-12">
                Хрупкий товар
            </v-select>
            <v-input v-model="$v.form.days_to_return.$model" :error="errorDaysToReturn" class="col-md-6 col-sm-12">
                Дней на возврат
            </v-input>
        </div>
        <hr>
        <div class="row">
            <v-select v-model="$v.form.explosive.$model" :options="booleanOptions" class="col-md-6 col-sm-12">
                Легковоспламеняющееся
            </v-select>
            <v-select v-model="$v.form.has_battery.$model" :options="booleanOptions" class="col-md-6 col-sm-12">
                В составе есть элемент питания
            </v-select>
            <v-select v-model="$v.form.gas.$model" :options="booleanOptions" class="col-md-6 col-sm-12">
                Газ
            </v-select>
        </div>
        <button @click="save" class="btn btn-dark" :disabled="!$v.form.$anyDirty">Сохранить</button>
    </div>
</template>

<script>
    import Helpers from "../../../../../../scripts/helpers";
    import Services from "../../../../../../scripts/services/services";

    import {validationMixin} from 'vuelidate';
    import {required, requiredIf, integer} from 'vuelidate/lib/validators';

    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';

    const formFields = ['has_battery', 'explosive', 'width', 'height', 'length', 'weight', 'gas', 'need_special_case',
        'need_special_store', 'fragile', 'days_to_return'];

    export default {
        mixins: [validationMixin],
        components: {
            VInput,
            VSelect,
        },
        props: {
            source: Object,
            options: Object,
        },
        data () {
            return {
                form: Object.assign({}, this.source),
                specialCaseRequired: !!this.source.need_special_case,
                specialStoreRequired: !!this.source.need_special_store,
            };
        },
        validations: {
            form: {
                width: {required, integer},
                height: {required, integer},
                length: {required, integer},
                weight: {required, integer},
                has_battery: {},
                explosive: {},
                gas: {},
                need_special_case: {
                    required: requiredIf(function () { return this.specialCaseRequired; })
                },
                need_special_store: {
                    required: requiredIf(function () { return this.specialStoreRequired; })
                },
                fragile: {},
                days_to_return: {integer},
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                let data = Helpers.filterObject(this.form, formFields);
                Services.net().post(this.getRoute('products.saveProduct', {id: this.source.id}), {}, data)
                    .then(()=> {
                        this.$emit('onSave');
                    });
            }
        },

        computed: {
            checkSpecialCase: {
                get() {
                    return !!this.form.need_special_case;
                },
                set(value) {
                    this.specialCaseRequired = value;
                    if (!value) {
                        this.$v.form.need_special_case.$model = null;
                    }
                }
            },
            checkSpecialStore: {
                get() {
                    return !!this.form.need_special_store;
                },
                set(value) {
                    this.specialStoreRequired = value;
                    if (!value) {
                        this.$v.form.need_special_store.$model = null;
                    }
                }
            },
            booleanOptions() {
                return [{value: 0, text: 'Нет'}, {value: 1, text: 'Да'}];
            },

            errorWidth() {
                if (this.$v.form.width.$dirty) {
                    if (!this.$v.form.width.required) return "Обязательное поле!";
                    if (!this.$v.form.width.integer) return "Только цифры!";
                }
            },
            errorHeight() {
                if (this.$v.form.height.$dirty) {
                    if (!this.$v.form.height.required) return "Обязательное поле!";
                    if (!this.$v.form.height.integer) return "Только цифры!";
                }
            },
            errorLength() {
                if (this.$v.form.length.$dirty) {
                    if (!this.$v.form.length.required) return "Обязательное поле!";
                    if (!this.$v.form.length.integer) return "Только цифры!";
                }
            },
            errorWeight() {
                if (this.$v.form.weight.$dirty) {
                    if (!this.$v.form.weight.required) return "Обязательное поле!";
                    if (!this.$v.form.weight.integer) return "Только цифры!";
                }
            },
            errorDaysToReturn() {
                if (this.$v.form.days_to_return.$dirty) {
                    if (!this.$v.form.days_to_return.integer) return "Только цифры!";
                }
            },
            errorSpecialCase() {
                if (this.$v.form.need_special_case.$dirty) {
                    if (!this.$v.form.need_special_case.required) return "Обязательное поле!";
                }
            },
            errorSpecialStore() {
                if (this.$v.form.need_special_store.$dirty) {
                    if (!this.$v.form.need_special_store.required) return "Обязательное поле!";
                }
            }
        }
    }
</script>