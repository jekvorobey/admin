<template>
    <layout-main back="true">
        <div class="container mt-4">

            <div class="row mt-4">
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
                                            @click="confirmSaving"
                                            class="btn btn-success btn-lg">
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
                                                {{ error }}<br/>
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
                                    class="btn btn-success btn-lg btn-block">
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
                              @change="changeType"
                              :options="availableTypes"
                              help="Обязательное поле">
                        Тип атрибута
                    </v-select>
                </div>

                <div class="col-6">
                    <div class="custom-control custom-checkbox">
                        <input v-model="productProperty.is_color"
                               type="checkbox"
                               class="custom-control-input"
                               id="isColor"
                               :disabled="productProperty.type !== 'directory'">
                        <label id="isColorPopover"
                               class="custom-control-label"
                               for="isColor">
                            Атрибут хранит цвет
                        </label>
                        <b-popover target="isColorPopover" triggers="hover" placement="top">
                            Только для атрибутов типа <em>Значение из списка</em>. <br/>
                            Необходимо дополнительно ввести коды цветов в формате #rrggbb
                        </b-popover>
                    </div>
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
                <div class="col-lg-6 col-sm-12">
                    <h4>Атрибут актуален для категорий:</h4>

                    <modal-categories-checkbox class="mt-4"
                                               :categories="formCategories.children"
                                               :model.sync="productProperty.categories"/>
                </div>

                <ValuesEditFrame :attribute-type="productProperty.type"
                                 :available-values="productProperty.values"
                                 :is-color="productProperty.is_color"
                                 :values-errors.sync="valuesErrors"/>
            </div>
        </div>

        <deletion-alert v-if="iProperty"
                        :propId="iProperty.id"
                        :type="iProperty.type"/>
        <values-reset-alert v-if="iProperty"
                            @proceed="save"/>
    </layout-main>
</template>

<script>
import VInput from '../../../components/controls/VInput/VInput.vue';
import VSelect from '../../../components/controls/VSelect/VSelect.vue';
import ModalCategoriesCheckbox from '../../Customer/Detail/components/modal-categories-checkbox.vue';
import DeletionAlert from './components/deletion-alert-modal.vue';
import ValuesResetAlert from './components/values-reset-alert-modal.vue';
import ValuesEditFrame from './components/attribute-values-edit.vue';
import NestedSets from '../../../../scripts/nestedSets.js';
import Services from '../../../../scripts/services/services.js';

export default {
    components: {
        VSelect,
        VInput,
        ModalCategoriesCheckbox,
        DeletionAlert,
        ValuesResetAlert,
        ValuesEditFrame
    },
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
                    new: [
                        {
                            name: '',
                            code: '#000000'
                        },
                        {
                            name: '',
                            code: '#000000'
                        }
                    ]
                }
            },
            valuesErrors: [],
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
                    this.iProperty.is_color ? value.code = '#' + value.code : value.code = '#000000';
                    this.productProperty.values.old.push(value)
                });
            }
        },
        save() {
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
                setTimeout(() => {
                    window.location = this.getRoute(
                        'products.properties.list', {}
                    )}, 1000);
            }, () => {
                Services.msg('Возникла ошибка при сохранении','danger')
            }).finally(() => {
                Services.hideLoader();
            })
        },
        confirmDeletion() {
            this.$bvModal.show('modal-deletion-alert');
        },
        confirmSaving() {
            if (this.iProperty.type !== this.productProperty.type) {
                this.$bvModal.show('modal-values-reset-alert');
            } else {
                this.save();
            }
        },
        changeType() {
            if (this.productProperty.type !== 'directory') {
                this.productProperty.is_color = false;
            }
        },
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
        formErrors() {
            let errors = [];
            if (!this.productProperty.name) {
                errors.push('• Введите название для административного раздела');
            }
            if (!this.productProperty.display_name) {
                errors.push('• Введите название для публичной части сайта');
            }
            if (!this.productProperty.type) {
                errors.push('• Укажите тип атрибута');
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