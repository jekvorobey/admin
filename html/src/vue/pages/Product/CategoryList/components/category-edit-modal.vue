<template>
    <b-modal id="category-edit-modal" hide-footer ref="modal" size="lg" @hidden="resetFields()">
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
                <b-row class="mb-2">
                    <b-col cols="3">
                        <label for="category-parent">Родительская категория</label>
                    </b-col>
                    <b-col cols="9">
                        <v-select
                                id="category-parent"
                                v-model="parentId"
                                :options="availableParents"
                        ></v-select>
                        <span v-if="warningParentField(parentId)" style="font-size: 85%">
                            <fa-icon icon="exclamation-triangle" class="text-warning"></fa-icon>
                            К выбранной родительской категории уже прикреплены товары, при создании дочерней категории все существующие товары перейдут в неё
                        </span>
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
                    'active': this.categoryToEdit.active,
                    'parent_id': this.parentId,
                };

                console.log(data);

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
            warningParentField() {
                let parent = this.categories.find((item) => item.id === this.parentId);
                if (!parent) {
                    return false;
                }
                // В случае, если потенциальная родительская категория является листом и имеет товары
                if (parent && parent.productsCount > 0 && this.getDescendant(parent).length === 0) {
                    return true;
                }
            },
            getDescendant(category) {
                let descendants = [];
                if (!category) return descendants;

                this.categories.forEach((item) => {
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
                let options = this.categories.filter((item) => {
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
                    this.mode = value ? 'edit' : 'create';
                    this.categoryToEdit = value ? value : newCategoryTemplate;
                    this.parentId = value ? value.parent_id : null;
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