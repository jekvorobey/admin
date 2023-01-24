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
                <hr>
                <div class="row">
                    <v-input v-model="$v.form.limit_qty.$model" :error="errorLimitQty" class="col-md-6 col-sm-12">
                        Ограничение в одни руки, шт
                    </v-input>
                    <v-input v-model="$v.form.limit_period.$model" :error="errorLimitPeriod" class="col-md-6 col-sm-12">
                        Период ограничения, ч.
                    </v-input>
                </div>
                <hr>
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
        'vendor_code', 'width', 'height', 'length', 'weight',
        'limit_qty', 'limit_period'
    ];
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
                limit_qty: {integer},
                limit_period: {integer},
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
            },
            categoryFullName(id) {
                if (!(id in this.categoriesObject)) {
                    return null;
                }

                let names = [];
                let currentCategory = this.categoriesObject[id];
                if (currentCategory.is_leaf) {
                    names.push(currentCategory.name);
                } else {
                    return;
                }

                while (true) {
                    let category = this.categoriesObject[currentCategory.id];
                    if (category.parent_id && category.parent_id in this.categoriesObject) {
                        let parentCategory = this.categoriesObject[category.parent_id];
                        if (parentCategory.depth >= currentCategory.depth) {
                            break;
                        }

                        names.push(parentCategory.name);
                        currentCategory = parentCategory;
                        continue;
                    }

                    break;
                }

                return names.reverse().join(' » ');
            },
        },
        computed: {
            brandOptions() {
                let brandList = Object.values(this.options.brands);
                return brandList.map(brand => ({value: brand.id, text: brand.name}));
            },
            categoriesObject() {
                return Object.fromEntries(Object.values(this.options.categories).map(category => [category.id, category]))
            },
            categoryOptions() {
                let categoryList = Object.values(this.options.categories);
                let resultCategoryList = [];

                categoryList.forEach(category => {
                    let categoryText = this.categoryFullName(category.id);
                    if (categoryText) {
                        resultCategoryList.push({
                            value: category.id,
                            text: categoryText
                        })
                    }
                });

                return resultCategoryList;
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
            },
            errorLimitQty() {
                if (this.$v.form.limit_qty.$dirty) {
                    if (!this.$v.form.limit_qty.integer) return "Только цифры!";
                }
            },
            errorLimitPeriod() {
                if (this.$v.form.limit_period.$dirty) {
                    if (!this.$v.form.limit_period.integer) return "Только цифры!";
                }
            }
        }
    }
</script>

<style scoped>

</style>