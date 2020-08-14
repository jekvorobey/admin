<template>
    <b-modal id="category-edit-modal" hide-footer ref="modal" size="xl" @hidden="resetFields()">
        <div slot="modal-title">
            <strong v-if="mode === 'create'">Создать новую категорию</strong>
            <strong v-else-if="mode === 'edit'">Редактировать категорию</strong>
        </div>
        <div class="card">
            <div class="card-body">
                <b-row class="mb-2">
                    <b-col cols="3">
                        <label for="category-name">Название категории</label>
                    </b-col>
                    <b-col cols="9">
                        <v-input id="category-name"
                               v-model="$v.categoryToEdit.name.$model"
                               class="mb-2"
                               :error="errorNameField()"
                        />
                    </b-col>
                </b-row>
                <!--<b-row class="mb-2">-->
                    <!--<b-col cols="4">-->
                        <!--<label for="category-code">Символьный код</label>-->
                        <!--<fa-icon icon="question-circle" v-b-popover.hover="codeTooltip"></fa-icon>-->
                    <!--</b-col>-->
                    <!--<b-col cols="8">-->
                        <!--<v-input id="category-code"-->
                               <!--v-model="$v.categoryToEdit.code.$model"-->
                               <!--class="mb-2"-->
                               <!--:error="errorCodeField()"-->
                        <!--/>-->
                    <!--</b-col>-->
                <!--</b-row>-->
                <b-row class="mb-2">
                    <b-col cols="3">
                        <label for="category-parent">Родительская категория</label>
                    </b-col>
                    <b-col cols="9">
                        <v-select
                                id="category-parent"
                                v-model="categoryToEdit.parent_id"
                                :options="availableParents"
                        ></v-select>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="3">
                        <label for="category-active">Активна</label>
                    </b-col>
                    <b-col cols="9">
                        <input id="category-active"
                               type="checkbox"
                               v-model="categoryToEdit.active" :disabled="canSetActive"/>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="3">
                        <label for="category-props">Доп. свойства</label>
                        <button @click="addField()"
                                class="btn btn-outline-info float-right h-25 ml-3">
                            <fa-icon icon="plus"></fa-icon>
                        </button>
                    </b-col>
                    <b-col cols="9">
                        <table v-if="category.props.length > 0" class="table-bordered">
                            <thead>
                            <tr class="d-flex">
                                <th class="col-sm-3">Название</th>
                                <th class="col-sm-3">Отображаемое название</th>
                                <th class="col-sm-2">Тип</th>
                                <th class="col-sm-2">Множественность</th>
                                <!--<th class="col-sm-1">Цвет</th>-->
                                <th class="col-sm-2">Показывать в фильтре</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="prop in category.props" class="d-flex">
                                <td class="col-sm-3">{{ prop.name }}</td>
                                <td class="col-sm-3">{{ prop.display_name }}</td>
                                <td class="col-sm-2">{{ propertyTypes[prop.type] }}</td>
                                <td class="col-sm-2">{{ prop.is_multiple ? 'Да' : 'Нет' }}</td>
                                <td class="col-sm-2">{{ prop.is_filterable ? 'Да' : 'Нет'}}</td>
                            </tr>
                            </tbody>
                        </table>
                        <span v-else id="category-props">Нет</span>
                    </b-col>
                </b-row>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-success"
                    @click="save">
                Сохранить
            </button>
            <button class="btn btn-outline-danger"
                    @click="closeModal">
                Отмена
            </button>
        </div>
    </b-modal>
</template>


<script>
    import Services from "../../../../../scripts/services/services";
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDate from '../../../../components/controls/VDate/VDate.vue';
    import { validationMixin } from 'vuelidate';
    import { required, alphaNum, helpers } from 'vuelidate/lib/validators';

    const newCategoryTemplate = {
        name: null,
        code: null,
        parent_id: null,
        active: false,
    };

    const defaultOptions = [{ value: null, text: 'Нет' }];

    const categoryCodeValidator = helpers.regex('code', /^[a-z\d_]*$/);

    export default {
        components: {
            VSelect,
            VInput,
            VDate
        },
        mixins: [validationMixin],
        props: {
            category: Object,
            collection: Array,
        },
        data() {
            return {
                mode: 'create',
                categoryToEdit: newCategoryTemplate,
                addedProperties: [],
                deletedProperties: [],
            }
        },
        validations() {
            return {
                categoryToEdit: {
                    name: {
                        required,
                        // alphaNum,
                    },
                    code: {
                        categoryCodeValidator,
                    },
                },
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                let data = {
                    'name': this.categoryToEdit.name,
                    // 'code': this.categoryToEdit.code ? this.categoryToEdit.code : null,
                    'parent_id': this.categoryToEdit.parent_id,
                    'active': this.categoryToEdit.active
                }

                console.log(data);

                Services.showLoader();
                let savePromise;
                if (this.mode === 'create') {
                    savePromise = this.createCategory(this.categoryToEdit);
                } else if (this.mode === 'edit') {
                    savePromise = this.editCategory(this.categoryToEdit);
                }

                savePromise.then((data) => {
                    Services.msg("Категория сохранена!");
                    this.$bvModal.hide('category-edit-modal');
                    setTimeout(window.location.reload.bind(window.location), 1000);
                }, () => {
                    Services.msg("Не удалось сохранить категорию", 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            createCategory(category) {
                return Services.net().post(this.getRoute('categories.create'), {}, category, {}, true);
            },
            editCategory(category) {
                return Services.net().put(this.getRoute('categories.update'), {}, category, {}, true);
            },
            closeModal() {
                this.$bvModal.hide('category-edit-modal');
            },
            resetFields() {
                console.log('resetted');
                // this.categoryToEdit = newCategoryTemplate;
                this.$v.$reset();

            },
            errorNameField() {
                if (this.$v.categoryToEdit.name.$dirty
                    && this.$v.categoryToEdit.name.$invalid) {
                    return "Введите корректное название";
                }
            },
            errorCodeField() {
                if (this.$v.categoryToEdit.code.$dirty
                    && this.$v.categoryToEdit.code.$invalid) {
                    return "Код может состоять только из символов нижнего регистра, цифр и нижнего подчеркивания";
                }
            },
            getDescendant(category) {
                let descendants = [];
                if (!category) return descendants;

                this.collection.forEach((item) => {
                    if (category.id === item.parent_id) {
                        descendants.push(item.id);
                        descendants = descendants.concat(this.getDescendant(item));
                    }
                });

                return descendants;
            },
        },
        computed: {
            availableParents() {
                let options = this.collection.filter((item) => {
                    return item.active;
                });
                if (this.category) {
                    let descendants = this.getDescendant(this.category);
                    options = options.filter((item) => {
                        return !descendants.includes(item.id) && this.category.id !== item.id;
                    })
                }

                return defaultOptions.concat(options.map((item) => {
                    return {
                        value: item.id,
                        text: item.name,
                    }
                }));
            },
            codeTooltip() {
                return 'Оставьте поле пустым, если хотите, чтобы код был сгенерирован автоматически';
            },
        },
        watch: {
            'category': {
                handler(value) {
                    console.log('watchh');
                    this.mode = value ? 'edit' : 'create';
                    this.categoryToEdit = value ? value : newCategoryTemplate;
                }
            },
        }
    }
</script>

<style scoped>
    .prop-name {
        width: 25%;
    }
</style>