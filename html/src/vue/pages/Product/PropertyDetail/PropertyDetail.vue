<template>
    <layout-main>
        <div class="container mt-4">
            <div class="row">
                <div class="card col-12">
                    <div class="card-body">
                        <template v-if="iProperty">
                            <div class="row">
                                <div class="col-6">
                                    <h5><em>{{ iProperty.name }}</em></h5>
                                    <small class="d-block text-muted">
                                        Последнее изменение: {{ datetimePrint(iProperty.updated_at)}}
                                    </small>
                                </div>

                                <div class="col-6 text-right" style="align-self: center">
                                    <button type="button"
                                            @click="confirmDeletion"
                                            class="btn btn-danger btn-lg mr-2">
                                        <fa-icon icon="trash-alt"/> Удалить атрибут
                                    </button>
                                    <button v-if="allErrors.length === 0"
                                            type="button"
                                            @click="save"
                                            class="btn btn-success btn-lg"
                                            :disabled="$v.$invalid">
                                        <fa-icon icon="check"/> Сохранить изменения
                                    </button>
                                    <template v-else>
                                        <span id="disabled-save-button"
                                              class="d-inline-block"
                                              tabindex="0">
                                            <b-button variant="success btn-lg"
                                                      style="pointer-events: none;"
                                                      disabled>
                                                <fa-icon icon="check"/> Сохранить изменения
                                            </b-button>
                                        </span>
                                        <b-tooltip target="disabled-save-button">
                                            <template v-for="error in allErrors">
                                                {{ error }}<br>
                                            </template>
                                        </b-tooltip>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <template v-if="allErrors.length > 0">
                                <span id="disabled-create-button"
                                      class="d-inline-block btn-block"
                                      tabindex="0">
                                    <b-button variant="success btn-lg btn-block"
                                              style="pointer-events: none;"
                                              disabled>
                                        <fa-icon icon="check"/> Создать товарный атрибут
                                    </b-button>
                                </span>
                                <b-tooltip target="disabled-create-button">
                                    <template v-for="error in allErrors">
                                        {{ error }}<br>
                                    </template>
                                </b-tooltip>
                            </template>

                            <button v-else type="button"
                                    @click="save"
                                    class="btn btn-success btn-lg btn-block"
                                    :disabled="$v.$invalid">
                                <fa-icon icon="check"/> Создать товарный атрибут
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <h4>Общие параметры товарного атрибута:</h4>
            </div>

            <div class="row mt-4">
                <div class="col-6">
                    <v-input v-model="productProperty.name" help="Обязательное поле">
                        Название атрибута для административного раздела
                    </v-input>
                </div>
                <div class="col-6">
                    <v-input v-model="productProperty.display_name" help="Обязательное поле">
                        Название атрибута для публичной части сайта
                    </v-input>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6">
                    <v-select v-model="productProperty.type"
                              :options="availableTypes"
                              help="Обязательное поле">
                        Тип атрибута
                    </v-select>
                </div>

                <div class="col-6">
                    <p class="mb-2">Особенности атрибута</p>
                    <div class="custom-control custom-checkbox">
                        <input v-model="productProperty.is_filterable"
                               type="checkbox"
                               class="custom-control-input"
                               id="isFilterable">
                        <label class="custom-control-label" for="isFilterable">
                            Выводить атрибут в фильтр товаров
                        </label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input v-model="productProperty.is_multiple"
                               type="checkbox"
                               class="custom-control-input"
                               id="isMultiple">
                        <label class="custom-control-label" for="isMultiple">
                            Атрибут хранит несколько значений
                        </label>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6">
                    <h4>Атрибут актуален для категорий:</h4>

                    <modal-categories-checkbox class="mt-4"
                                               :categories="formCategories.children"
                                               :model.sync="productProperty.categories"/>
                </div>

                <ValuesEditFrame :attribute-type="productProperty.type"
                                 :available-values="productProperty.values"/>
            </div>
        </div>

        <deletion-alert v-if="iProperty"
                        :propId="iProperty.id"
                        :type="iProperty.type"/>
    </layout-main>
</template>

<script>
import VInput from '../../../components/controls/VInput/VInput.vue';
import VSelect from '../../../components/controls/VSelect/VSelect.vue';
import ModalCategoriesCheckbox from '../../Customer/Detail/components/modal-categories-checkbox.vue';
import DeletionAlert from './components/deletion-alert-modal.vue';
import ValuesEditFrame from './components/attribute-values-edit.vue';
import NestedSets from '../../../../scripts/nestedSets.js';
import Services from '../../../../scripts/services/services.js';

import {validationMixin} from 'vuelidate';
import {required} from 'vuelidate/lib/validators';

export default {
    components: {
        VSelect,
        VInput,
        ModalCategoriesCheckbox,
        DeletionAlert,
        ValuesEditFrame
    },
    mixins: [validationMixin],
    props: ['iProperty', 'iCategories', 'property_types'],
    data() {
        return {
            productProperty: {
                id: null,
                name: '',
                display_name: '',
                type: null,
                is_filterable: false,
                is_multiple: false,
                is_color: false,
                categories: [],
                values: {
                    old: [],
                    new: ['', '']
                }
            },
        }
    },
    validations: {
        productProperty: {
            name: {required},
            display_name: {required},
            type: {required}
        }
    },
    methods: {
        prepareForEdit() {
            if (this.iProperty) {
                this.productProperty.id = this.iProperty.id;
                this.productProperty.name = this.iProperty.name;
                this.productProperty.display_name = this.iProperty.display_name;
                this.productProperty.type = this.iProperty.type;
                this.productProperty.is_filterable = this.iProperty.is_filterable;
                this.productProperty.is_multiple = this.iProperty.is_multiple;
                this.productProperty.is_color = this.iProperty.is_color;

                this.iProperty.categoryPropertyLinks.forEach(link => {
                    this.productProperty.categories.push(link.category_id)
                });
                this.iProperty.values.forEach(value => {
                    this.productProperty.values.old.push(value)
                });
            }
        },
        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            Services.showLoader();
            Services.net().put(this.getRoute('products.properties.update', {}), {}, {
                id: this.productProperty.id,
                name: this.productProperty.name,
                display_name: this.productProperty.display_name,
                type: this.productProperty.type,
                is_filterable: Number(this.productProperty.is_filterable),
                is_multiple: Number(this.productProperty.is_multiple),
                is_color: Number(this.productProperty.is_color),
                categories: JSON.stringify(this.productProperty.categories),
                old_values: JSON.stringify(this.productProperty.values.old),
                new_values: JSON.stringify(this.productProperty.values.new),
            }
            ).then(() => {
                Services.msg('Информация о товарном атрибуте успешно сохранена')
              window.location.reload();
            }, () => {
                Services.msg('Возникла ошибка при сохранении','danger')
            }).finally(() => {
                Services.hideLoader();
            })
        },
        confirmDeletion() {
            this.$bvModal.show('modal-deletion-alert');
        }
    },
    computed: {
        formCategories() {
            return NestedSets.process(this.iCategories);
        },
        availableTypes() {
            return Object.entries(this.property_types).map(type => ({
                value: type[0],
                text: type[1]
            }));
        },
        /// ОШИБКИ ///
        valuesErrors() {
            let errors = [];
            if (this.productProperty.type === 'directory') {
                if (this.productProperty.values.old.length === 0) {
                    if (
                        this.productProperty.values.new[0].length === 0
                        || this.productProperty.values.new[1].length === 0
                    ) {
                        errors.push('• Укажите не менее 2 возможных значений\n\n');
                    }
                }
                if (this.productProperty.values.old.length === 1) {
                    if (this.productProperty.values.new[0].length === 0) {
                        errors.push('• Добавьте хотя бы 1 новое значение\n\n');
                    }
                }
                this.productProperty.values.old.forEach(item => {
                    if (item.name.length === 0) {
                        errors.push('• Введите значение на замену для старого значения\n\n');
                    }
                })
            }
            return errors;
        },
        formErrors() {
            let errors = [];
            if (!this.productProperty.name) {
                errors.push('• Введите название для административного раздела\n\n');
            }
            if (!this.productProperty.display_name) {
                errors.push('• Введите название для публичной части сайта\n\n');
            }
            if (!this.productProperty.type) {
                errors.push('• Укажите тип атрибута\n\n');
            }
            return errors;
        },
        allErrors() {
            return this.formErrors.concat(this.valuesErrors)
        }
    },
    created() {
        this.prepareForEdit();
    }
}
</script>

<style scoped>

</style>