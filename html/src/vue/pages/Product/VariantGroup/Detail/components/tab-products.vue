<template>
    <div>
        <b-row class="d-flex justify-content-between mt-3 mb-3">
            <b-col class="col-md-3">
                <products-search :model.sync="newProducts"></products-search>
            </b-col>
            <b-col>
                <button
                        class="btn btn-success"
                        @click="addProducts"
                >
                    <fa-icon icon="plus"></fa-icon> Добавить товар
                </button>
                <v-delete-button
                        @delete="deleteProducts(selectedProductIds)"
                        btn-class="btn-danger"
                        v-if="selectedProductIds.length > 0" class="ml-3"
                />
            </b-col>
        </b-row>
        <b-table-simple hover small caption-top responsive>
            <b-thead>
                <b-tr>
                    <b-th></b-th>
                    <b-th>ID<br><small>Дата создания</small></b-th>
                    <b-th>Фото</b-th>
                    <b-th class="with-small">Название<br><small>Артикул</small></b-th>
                    <b-th class="with-small">Характеристика<br><small>Значение</small></b-th>
                    <b-th class="with-small">Категория<br><small>Бренд</small></b-th>
                    <b-th class="with-small">Количество<br><small>Вес 1 шт</small><br><small>ДxШxВ 1 шт</small></b-th>
                    <b-th>Цена</b-th>
                    <b-th>Статус товара</b-th>
                    <b-th></b-th>
                </b-tr>
            </b-thead>
            <b-tbody>
                <template v-if="products.length > 0">
                    <tr v-for="product in products">
                        <b-td>
                            <input type="checkbox" value="true" :value="product.id" v-model="selectedProductIds">
                        </b-td>
                        <b-td>
                            {{ product.id }}<br>
                            <small>{{ product.created_at }}</small>
                        </b-td>
                        <b-td><img :src="productPhoto(product)" class="preview" :alt="product.name" v-if="product.mainImage"></b-td>
                        <b-td class="with-small">
                            <a :href="getRoute('products.detail', {id: product.id})" target="_blank">
                                {{ product.name }}
                            </a><br>
                            <small>{{ product.vendor_code }}</small>
                        </b-td>
                        <b-td>
                            <p v-for="property in product.gluedProperties">
                                {{property.property.name}} (ID: {{property.property_id}})<br>
                                <small>
                                    {{property.propertyDirectoryValue.name}} (ID: {{property.propertyDirectoryValue.id}})
                                </small>
                            </p>
                        </b-td>
                        <b-td class="with-small">
                            {{ product.category ? product.category.name : ''}}<br>
                            <small>{{ product.brand ? product.brand.name : ''}}</small>
                        </b-td>
                        <b-td class="with-small">
                            {{ product.qty | integer }} шт<br>
                            <small> {{product.weight}} г</small><br>
                            <small> {{product.length}} x {{product.width}} x {{product.height}} мм</small>
                        </b-td>
                        <b-td>{{ preparePrice(product.price) }} руб</b-td>
                        <b-td>{{ product.approval_status ? product.approval_status.name : '' }}</b-td>
                        <b-td><v-delete-button @delete="deleteProducts([product.id])" btn-class="btn-danger"/></b-td>
                    </tr>
                </template>
            </b-tbody>
        </b-table-simple>
    </div>
</template>
<script>
    import Services from '../../../../../../scripts/services/services';
    import VDeleteButton from '../../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import ProductsSearch from '../../../../../components/search/products-search.vue';

    export default {
        props: {
            model: {},
        },
        components: {
            ProductsSearch,
            VDeleteButton,
        },
        data() {
            return {
                products: [],
                newProducts: {},
                selectedProductIds: [],
            }
        },
        methods: {
            productPhoto(product) {
                return '/files/compressed/' + product.mainImage.file_id + '/50/50/webp';
            },
            addProducts() {
                let newProductIds = Object.keys(this.newProducts);
                if (!newProductIds.length) {
                    Services.msg("Выберите товары", "danger");
                    return;
                }

                Services.showLoader();
                Services.net().post(this.getRoute('variantGroups.detail.products.add', {id: this.variantGroup.id}), {
                    productIds: newProductIds,
                }).then((data) => {
                    this.products = data.products;
                    this.newProducts = {};
                    Services.msg("Добавление товара(ов) прошло успешно");
                }, () => {
                    Services.msg("Ошибка при добавлении товара(ов) - возможно товар(ы) не имеет(ют) указанных характеристик для склейки", "danger");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            deleteProducts(productIds) {
                Services.showLoader();
                Services.net().delete(this.getRoute('variantGroups.detail.products.delete', {id: this.variantGroup.id}), {
                    productIds: productIds,
                }).then((data) => {
                    this.products = data.products;
                    Services.msg("Удаление товара(ов) прошло успешно");
                }, () => {
                    Services.msg("Ошибка при удалении товара(ов)", "danger");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        computed: {
            variantGroup: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('variantGroups.detail.products.load', {id: this.model.id})).then(data => {
                this.products = data.products;
            }).finally(() => {
                Services.hideLoader();
            });
        }
    }
</script>
