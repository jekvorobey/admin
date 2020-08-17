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
                                    <button type="button"
                                            @click="save"
                                            class="btn btn-success btn-lg"
                                            :disabled="$v.$invalid">
                                        <fa-icon icon="check"/> Сохранить изменения
                                    </button>
                                </div>
                            </div>
                        </template>

                        <button v-else type="button"
                                @click="save"
                                class="btn btn-success btn-lg btn-block"
                                :disabled="$v.$invalid">
                            <fa-icon icon="check"/> Создать товарный атрибут
                        </button>
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

                    <div class="custom-control custom-checkbox">
                        <input v-model="productProperty.is_color"
                               type="checkbox"
                               class="custom-control-input"
                               id="isColor">
                        <label class="custom-control-label" for="isColor">
                            Атрибут хранит цвет
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

                <div class="col-6">
                    <h4 class="text-muted mb-4">Атрибут может принимать значения:</h4>

                    <em class="text-muted">
                        Будет готово в рамках задачи по созданию справочника значений для товарных атрибутов
                    </em>
                    <v-input class="mt-4" placeholder="Значение-заглушка 1" disabled/>
                    <v-input placeholder="Значение-заглушка 2" disabled/>
                    <v-input placeholder="Значение-заглушка 3" disabled/>
                    <button class="btn btn-dark float-right" disabled>
                        <fa-icon icon="plus"/> Добавить значение
                    </button>
                </div>
            </div>
        </div>

        <deletion-alert v-if="iProperty"
                        :propId="iProperty.id"
                        :type="iProperty.type"/>
    </layout-main>
</template>

<script>
import VInput from "../../../components/controls/VInput/VInput.vue";
import VSelect from "../../../components/controls/VSelect/VSelect.vue";
import ModalCategoriesCheckbox from "../../Customer/Detail/components/modal-categories-checkbox.vue"
import DeletionAlert from "./components/deletion-alert-modal.vue"
import NestedSets from "../../../../scripts/nestedSets.js";
import Services from "../../../../scripts/services/services.js";

import {validationMixin} from 'vuelidate';
import {required} from 'vuelidate/lib/validators';

export default {
    components: {VSelect, VInput, ModalCategoriesCheckbox, DeletionAlert},
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
                categories: []
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

                this.iProperty.categoryPropertyLinks.forEach(item => {
                    this.productProperty.categories.push(item.category_id)
                })
            }
        },
        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            Services.showLoader();
            Services.net().put(this.getRoute('products.properties.update', {}), {
                id: this.productProperty.id,
                name: this.productProperty.name,
                display_name: this.productProperty.display_name,
                type: this.productProperty.type,
                is_filterable: Number(this.productProperty.is_filterable),
                is_multiple: Number(this.productProperty.is_multiple),
                is_color: Number(this.productProperty.is_color),
                categories: JSON.stringify(this.productProperty.categories)
            }
            ).then(() => {
                Services.msg('Информация о товарном атрибуте успешно сохранена')
                setTimeout(() => {
                    window.location = this.getRoute(
                        'products.properties.list', {}
                    )}, 1500);
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
    },
    created() {
        this.prepareForEdit();
    }
}
</script>

<style scoped>

</style>