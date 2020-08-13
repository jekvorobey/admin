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
                    <b-col cols="4">
                        <label for="category-parent">Родительская категория</label>
                    </b-col>
                    <b-col cols="8">
                        <v-select
                                id="category-parent"
                                v-model="categoryToEdit.parent_id"
                                :options="availableParents"
                        ></v-select>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label for="category-active">Активна</label>
                    </b-col>
                    <b-col cols="8">
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
        code: null,
        parent_id: null,
        active: false,
    };

    const defaultOptions = [
        {
            value: null,
            text: 'Нет'
        }
    ];

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

                // console.log(this.$v);
                // console.log(this.availableParents);
                // console.log(this.collection);
                // console.log(this.category);
                // console.log(this.categoryToEdit);


                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                // console.log(this.categoryToEdit);

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
            // availableStores() {
            //     let chosen = (this.newOffer.stocks || []).map((value) => {
            //         return value.store_id;
            //     });
            //     return Object.values(this.stocks).filter((value) => {
            //         return !chosen.includes(value.id);
            //     }).map((value) => {
            //         return {value: parseInt(value.id), text: value.name};
            //     });
            // },
            // countedQty() {
            //     if (this.loading) return null;
            //
            //     let total = this.newOffer.stocks.reduce((total, stock) => {
            //         return total + (parseInt(stock.qty) || 0);
            //     }, 0);
            //     if (!total) {
            //         this.newOffer.status = '';
            //     }
            //     return total;
            // },
            // modalStatuses() {
            //     let saleStatuses;
            //     switch (this.mode) {
            //         case 'create':
            //             saleStatuses = this.offerCreateSaleStatuses;
            //             break;
            //         case 'edit':
            //             saleStatuses = this.offerEditSaleStatuses;
            //             break;
            //     }
            //     return Object.values(saleStatuses).map((val) => {
            //         return {value: parseInt(val.id), text: val.name};
            //     });
            // },
            // displayDateSelect() {
            //     this.$v.sale_at.$reset();
            //     return this.newOffer.status
            //         && this.offerCountdownSaleStatuses.includes(this.newOffer.status);
            // },
            // stocksTooltip() {
            //     return 'Добавьте нужный склад и введите количество имеющегося на нём товара'
            // }
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