<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                Редактирование Товара
            </div>
            <div slot="body">
                <v-input v-model="$v.form.name.$model" :error="errorName">
                    Название товара
                </v-input>
                <div class="row">
                    <v-input v-model="$v.form.vendor_code.$model" :error="errorVendorCode" class="col-md-6 col-sm-12">
                        Артикул
                    </v-input>
                    <v-select v-model="$v.form.approval_status.$model" :options="approvalStatusOptions" class="col-md-6 col-sm-12">
                        Статус проверки
                    </v-select>
                </div>
                <hr>
                <v-select v-model="$v.form.brand_id.$model" :options="brandOptions">
                    Бренд
                </v-select>
                <v-select v-model="$v.form.category_id.$model" :options="categoryOptions">
                    Категория
                </v-select>
                <hr>
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
                    <v-select v-model="$v.form.explosive.$model" :options="booleanOptions" class="col-md-6 col-sm-12">
                        Легковоспламеняющееся
                    </v-select>
                    <v-select v-model="$v.form.has_battery.$model" :options="booleanOptions" class="col-md-6 col-sm-12">
                        В составе есть элемент питания
                    </v-select>
                </div>
                <button @click="save" class="btn btn-dark" :disabled="!$v.form.$anyDirty">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import { validationMixin } from 'vuelidate';
    import { integer, required } from 'vuelidate/lib/validators';

    import Helpers from '../../../../../scripts/helpers';
    import Services from '../../../../../scripts/services/services';

    import modal from '../../../../components/controls/modal/modal.vue';

    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';

    import modalMixin from '../../../../mixins/modal.js';

    const formFields = ['has_battery', 'explosive', 'name', 'brand_id', 'category_id', 'approval_status',
        'vendor_code', 'width', 'height', 'length', 'weight'];
    export default {
        components: {
            modal,
            VInput,
            VSelect,
        },
        mixins: [modalMixin, validationMixin],
        props: {
            modalName: String,
            source: Object,
            options: Object,
        },
        data () {
            return {
                form: Object.assign({}, this.source),
            };
        },
        validations: {
            form: {
                name: {required},
                brand_id: {required},
                category_id: {required},
                approval_status: {required},
                vendor_code: {required},
                width: {required, integer},
                height: {required, integer},
                length: {required, integer},
                weight: {required, integer},
                has_battery: {},
                explosive: {},
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
                        this.closeModal();
                    });
            }
        },
        computed: {
            brandOptions() {
                let brandList = Object.values(this.options.brands);
                return brandList.map(brand => ({value: brand.id, text: brand.name}));
            },
            categoryOptions() {
                let categoryList = Object.values(this.options.categories);
                return categoryList.map(category => ({value: category.id, text: category.name}));
            },
            approvalStatusOptions() {
                return Object.entries(this.options.approval).map(status => ({value: status[0], text: status[1]}));
            },
            booleanOptions() {
                return [{value: 0, text: 'Нет'}, {value: 1, text: 'Да'}];
            },

            errorName() {
                if (this.$v.form.name.$dirty) {
                    if (!this.$v.form.name.required) return "Обязательное поле!";
                }
            },
            errorVendorCode() {
                if (this.$v.form.vendor_code.$dirty) {
                    if (!this.$v.form.vendor_code.required) return "Обязательное поле!";
                }
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
            }
        }
    }
</script>

<style scoped>

</style>