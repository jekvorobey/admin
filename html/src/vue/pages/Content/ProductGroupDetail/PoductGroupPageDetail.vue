<template>
    <layout-main back>
        <b-form @submit.prevent="submit">
            <b-form-group
                    label="Наименование*"
                    label-for="group-name"
            >
                <b-form-input
                        id="group-name"
                        v-model="productGroup.name"
                        type="text"
                        required
                        placeholder="Введите наименование"
                />
            </b-form-group>

            <b-form-group
                    label="Символьный код*"
                    label-for="group-code"
            >
                <b-form-input
                        id="group-code"
                        v-model="productGroup.code"
                        type="text"
                        required
                        placeholder="Введите символьный код"
                />
            </b-form-group>

            <b-form-group
                    label="Активность"
                    label-for="group-active"
            >
                <b-form-checkbox
                        id="group-active"
                        v-model="productGroup.active"
                        :value="1"
                        :unchecked-value="0"
                        required
                />
            </b-form-group>

            <b-form-group
                    label="Наличие в списках"
                    label-for="group-is-shown"
            >
                <b-form-checkbox
                        id="group-is-shown"
                        v-model="productGroup.is_shown"
                        :value="1"
                        :unchecked-value="0"
                        required
                />
            </b-form-group>

            Превью картинка*<br>
            <img v-if="previewPhoto"
                 :src="previewPhoto.url"
                 class="preview-photo"
            >
            <file-input
                    @uploaded="onUploadPreviewPhoto"
                    class="preview-photo-input"
            />

            <b-form-group
                    label="Тип*"
                    label-for="group-type"
            >
                <b-form-select
                        v-model="productGroup.type_id"
                        id="group-type"
                >
                    <b-form-select-option
                            :value="type.id"
                            v-for="type in productGroupTypes"
                            :key="type.id"
                    >
                        {{ type.name }}
                    </b-form-select-option>
                </b-form-select>
            </b-form-group>

            <b-form-group
                    label="Категория"
                    label-for="group-categories"
            >
                <b-form-select
                        v-model="productGroup.category_code"
                        id="group-category"
                >
                    <b-form-select-option
                            :value="category.code"
                            v-for="category in categories"
                            :key="category.code"
                    >
                        {{ category.name }}
                    </b-form-select-option>
                </b-form-select>
            </b-form-group>

            <select-filters
                    :i-selected-filters="selectedFilters"
                    :i-selected-category="productGroup.category_code"
                    @update="(data) => selectedFilters = data"
            >
            </select-filters>

            <select-products
                    :i-selected-product-ids="pluckSelectedProductIds()"
                    @update="onUpdateSelectedProducts"
            >
            </select-products>

            <b-button type="submit" class="mt-3" variant="dark">{{ isCreatingMode ? 'Создать' : 'Обновить' }}</b-button>
        </b-form>
    </layout-main>
</template>

<script>
    import { mapActions } from 'vuex';
    import Services from '../../../../scripts/services/services';
    import FileInput from '../../../components/controls/FileInput/FileInput.vue';
    import SelectFilters from './components/select-filters.vue';
    import SelectProducts from './components/select-products.vue';

    export default {
        components: {
            SelectFilters,
            SelectProducts,
            FileInput
        },
        props: {
            iProductGroup: {},
            iProductGroupTypes: {},
            iProductGroupImages: {},
            iCategories: {},
            options: {}
        },
        data() {
            return {
                productGroup: this.normalizeProductGroup(this.iProductGroup),
                productGroupTypes: this.iProductGroupTypes,
                productGroupImages: this.iProductGroupImages,
                categories: this.iCategories,
                selectedFilters: this.iProductGroup.filters,
                selectedProductIds: [],
                selectedProducts: this.iProductGroup.products,
            };
        },

        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            normalizeProductGroup(source) {
                return {
                    id: source.id ? source.id : null,
                    name: source.name ? source.name : null,
                    code: source.code ? source.code : null,
                    active: source.active ? source.active : false,
                    is_shown: source.is_shown ? source.is_shown : false,
                    type_id: source.type_id ? source.type_id : null,
                    preview_photo_id: source.preview_photo_id ? source.preview_photo_id : null,
                };
            },
            submit() {
                if (this.isCreatingMode) {
                    this.create();
                }
                else {
                    this.update();
                }
            },
            update() {
                let model = this.productGroup;
                model.filters = this.selectedFilters;
                model.products = this.selectedProducts;

                Services.net()
                    .put(this.getRoute('productGroup.update', {id: this.productGroup.id,}), {}, model)
                    .then((data) => {
                        this.showMessageBox({title: 'Изменения сохранены'});
                        window.location.href = this.route('productGroup.listPage');
                    })
                    .catch(() => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    });
            },
            create() {
                let model = this.productGroup;
                model.filters = this.selectedFilters;
                model.products = this.selectedProducts;

                Services.net()
                    .post(this.getRoute('productGroup.create'), {}, model)
                    .then((data) => {
                        this.showMessageBox({title: 'Страница сохранена'});
                        window.location.href = this.route('productGroup.listPage');
                    })
                    .catch(() => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    });
            },
            onUploadPreviewPhoto(file) {
                this.productGroupImages[file.id] = file;
                this.productGroup.preview_photo_id = file.id;
            },
            onUpdateSelectedProducts(data) {
                for (let productKey in data) {
                    let productId = data[productKey];

                    this.selectedProducts.push({
                        product_id: productId,
                        product_group_id: this.productGroup.id,
                    });
                }
            },
            pluckSelectedProductIds() {
                let ids = [];

                for (let productKey in this.selectedProducts) {
                    let product = this.selectedProducts[productKey];
                    ids.push(product.product_id);
                }

                return ids;
            }
        },
        computed: {
            previewPhoto() {
                const fileId = this.productGroup.preview_photo_id;

                if (fileId) {
                    const file = this.productGroupImages[fileId];

                    return file ? file : null;
                }

                return null;
            },
            isCreatingMode() {
                return this.productGroup.id === null;
            },
        },
    };
</script>

<style scoped>
    .preview-photo {
        width: 300px;
    }

    .preview-photo-input {
        width: 300px;
        margin-top: 10px;
    }
</style>
