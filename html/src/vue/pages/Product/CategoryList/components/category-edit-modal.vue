<template>
    <b-modal id="category-edit-modal" hide-footer ref="modal" size="lg" @hidden="resetFields()">
        <div slot="modal-title">
            <strong v-if="mode === 'create'">Создать новую категорию</strong>
            <strong v-else-if="mode === 'edit'">Редактировать категорию</strong>
        </div>
        <div class="card">
            <div class="card-body">
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label for="category-name">Название категории</label>
                    </b-col>
                    <b-col cols="8">
                        <v-input id="category-name"
                               v-model="$v.categoryToEdit.name.$model"
                               class="mb-2"
                               :error="errorNameField()"
                        />
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label for="category-code">
                            Символьный код
                            <fa-icon v-if="mode === 'create'" icon="question-circle" v-b-popover.hover="codeTooltip"></fa-icon>
                        </label>
                    </b-col>
                    <b-col cols="8">
                        <v-input id="category-code"
                                 v-model="$v.categoryToEdit.code.$model"
                                 class="mb-2"
                                 :error="errorCodeField()"
                        />
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label for="category-parent">Родительская категория</label>
                    </b-col>
                    <b-col cols="8">
                        <v-select
                                id="category-parent"
                                v-model="parentId"
                                :options="availableParents"
                        ></v-select>
                        <div class="warning" v-if="warningProductsMove" style="font-size: 85%">
                            <fa-icon icon="exclamation-triangle" class="text-warning"></fa-icon>
                            К выбранной родительской категории уже прикреплены товары, при создании дочерней категории все существующие товары перейдут в неё
                        </div>
                        <div class="warning" v-if="warningPropsRemove.length > 0" style="font-size: 85%">
                            <fa-icon icon="exclamation-triangle" class="text-warning"></fa-icon>
                            При изменении родительской категории у товаров текущей и дочерних категорий будут удалены следующие свойства:
                            <ul>
                                <li v-for="prop in warningPropsRemove">{{ prop.name }}</li>
                            </ul>
                        </div>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label for="category-active">Активна</label>
                    </b-col>
                    <b-col cols="8">
                        <input id="category-active"
                               type="checkbox"
                               v-model="categoryToEdit.active" :disabled="parentDisabled"/>
                    </b-col>
                </b-row>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-success"
                    :disabled="$v.categoryToEdit.$invalid"
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
    import { required, requiredIf, helpers } from 'vuelidate/lib/validators';

    const newCategoryTemplate = {
        name: null,
        code: null,
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
                parentId: null,
                categories: this.collection,
            }
        },
        validations() {
            return {
                categoryToEdit: {
                    name: {
                        required,
                    },
                    code: {
                        categoryCodeValidator,
                        required: requiredIf(function () {
                            return this.mode === 'edit';
                        }),
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
                    'code': this.categoryToEdit.code,
                    'active': this.categoryToEdit.active,
                    'parent_id': this.parentId,
                };
                Services.showLoader();
                let savePromise;
                if (this.mode === 'create') {
                    savePromise = this.createCategory(data);
                } else if (this.mode === 'edit') {
                    data['id'] = this.category.id;
                    savePromise = this.editCategory(data);
                }

                savePromise.then(() => {
                    Services.msg("Категория сохранена!");
                    this.$bvModal.hide('category-edit-modal');
                    setTimeout(window.location.reload.bind(window.location), 1000);
                }, () => {
                    Services.msg("Не удалось сохранить категорию", 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            createCategory(data) {
                return Services.net().post(this.getRoute('categories.create'), {}, data, {}, true);
            },
            editCategory(data) {
                return Services.net().put(this.getRoute('categories.update'), {}, data, {}, true);
            },
            closeModal() {
                this.$bvModal.hide('category-edit-modal');
            },
            resetFields() {
                let data = this.category ? this.category : newCategoryTemplate;
                this.categoryToEdit = Object.assign({}, data);
                this.parentId = this.category ? this.category.parent_id : null;
                this.$v.$reset();
            },
            errorNameField() {
                if (this.$v.categoryToEdit.name.$dirty
                    && this.$v.categoryToEdit.name.$invalid) {
                    return "Введите корректное название";
                }
            },
            errorCodeField() {
                if (this.$v.categoryToEdit.code.$dirty) {
                    if (!this.$v.categoryToEdit.code.categoryCodeValidator) {
                        return "Код может состоять только из символов нижнего регистра, цифр и нижнего подчеркивания";
                    }
                    if (!this.$v.categoryToEdit.code.required) {
                        return "Введите код";
                    }
                }
            },
            getAllProps(category) {
                if (!category) {
                    return [];
                }
                let props = category.properties;
                category.ancestors.forEach((id) => {
                    let ancestor = this.categories.find((item) => item.id === id);
                    props = props.concat(ancestor.properties);
                });
                return props;
            },
        },
        computed: {
            availableParents() {
                let options = this.categories;
                if (this.category) {
                    options = options.filter((item) => {
                        return !this.category.descendants.includes(item.id) && this.category.id !== item.id;
                    })
                }
                return defaultOptions.concat(options.map((item) => {
                    let ancestors = item.ancestors;
                    ancestors = ancestors.map(x => options.find(y => y.id === x).name);
                    ancestors.push(item.name);
                    return {
                        value: item.id,
                        text: ancestors.join(' >> '),
                    }
                }));
            },
            warningProductsMove() {
                let parent = this.categories.find((item) => item.id === this.parentId);
                if (!parent) {
                    return false;
                }
                // В случае, если потенциальная родительская категория является листом и имеет товары
                if (parent && parent.productsCount > 0 && parent.descendants.length === 0) {
                    return true;
                }
            },
            warningPropsRemove() {
                let oldParent = this.categories.find((item) => item.id === this.categoryToEdit.parent_id);
                let newParent = this.categories.find((item) => item.id === this.parentId);
                let oldProps = this.getAllProps(oldParent);
                let newProps = this.getAllProps(newParent);
                let newPropsIds = newProps.map((prop) => prop.id);

                return oldProps.filter((prop) => !newPropsIds.includes(prop.id));
            },
            parentDisabled() {
                if (this.parentId) {
                    let parent = this.categories.find((item) => item.id === this.parentId);
                    if (!parent.active) {
                        this.categoryToEdit.active = false;
                        return true;
                    }
                }
                return false;
            },
            codeTooltip() {
                return 'Оставьте поле пустым, если хотите, чтобы код был сгенерирован автоматически';
            },
        },
        watch: {
            'category': {
                handler(value) {
                    this.mode = value ? 'edit' : 'create';
                    let data = value ? value : newCategoryTemplate;
                    this.categoryToEdit = Object.assign({}, data);
                    this.parentId = value ? value.parent_id : null; //
                }
            },
        }
    }
</script>

<style scoped>
    .prop-name {
        width: 25%;
    }
    .warning {
        margin-bottom: 0.5rem;
    }
</style>