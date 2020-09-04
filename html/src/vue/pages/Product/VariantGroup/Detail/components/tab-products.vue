<template>
    <div>
        <b-row class="d-flex justify-content-between mt-3 mb-3">
            <template v-if="variantGroup.properties_count > 0">
                <b-col class="col-md-3">
                    <products-search
                            :model.sync="newProducts"
                            :excepted-ids="exceptedIds"
                            :merchant-id="parseInt(variantGroup.merchant_id ? variantGroup.merchant_id : 0)"
                    ></products-search>
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
            </template>
            <template v-else>
                <b-col>Сначала добавьте <span class="btn-link cursor-pointer" @click="go2PropertiesTab">характеристики</span> для
                    склейки товаров</b-col>
            </template>
        </b-row>
        <b-table-simple hover small caption-top responsive v-if="products.length > 0">
            <b-thead>
                <b-tr>
                    <b-th></b-th>
                    <b-th class="with-small">ID<br><small>Дата создания</small></b-th>
                    <b-th>Фото</b-th>
                    <b-th class="with-small">Название<br><small>Артикул</small></b-th>
                    <b-th class="with-small">Характеристика<br><small>Значение</small></b-th>
                    <b-th class="with-small">Категория<br><small>Бренд</small></b-th>
                    <b-th class="with-small">Количество<br><small>Вес 1 шт</small><small>ДxШxВ 1 шт</small></b-th>
                    <b-th>Цена</b-th>
                    <b-th>Статус товара</b-th>
                    <b-th></b-th>
                </b-tr>
            </b-thead>
            <b-tbody>
                <tr v-for="product in products"
                        :class="product.id === variantGroup.main_product_id ? 'table-primary' : ''"
                        :title="product.id === variantGroup.main_product_id ? 'Основной товар' : ''"
                >
                    <b-td>
                        <input type="checkbox" value="true" :value="product.id" v-model="selectedProductIds">
                    </b-td>
                    <b-td class="with-small">
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
                                <template v-if="property.propertyDirectoryValue">
                                    {{property.propertyDirectoryValue.name}} (ID: {{property.propertyDirectoryValue.id}})
                                    <div v-if="property.property.is_color" :style="'background-color:#' +
                                    property.propertyDirectoryValue.code" class="property-color"></div>
                                </template>
                                <template v-else>{{property.value}}</template>
                            </small>
                        </p>
                    </b-td>
                    <b-td class="with-small">
                        {{ product.category ? product.category.name : ''}}<br>
                        <small>{{ product.brand ? product.brand.name : ''}}</small>
                    </b-td>
                    <b-td class="with-small">
                        {{ product.qty | integer }} шт<br>
                        <small> {{product.weight}} г</small>
                        <small> {{product.length}} x {{product.width}} x {{product.height}} мм</small>
                    </b-td>
                    <b-td>{{ preparePrice(product.price) }} руб</b-td>
                    <b-td>{{ product.approval_status ? product.approval_status.name : '' }}</b-td>
                    <b-td>
                        <button
                                class="btn btn-primary"
                                title="Сделать основным товаром"
                                @click="setMainProduct(product.id)"
                                v-if="product.id !== variantGroup.main_product_id"
                        >
                            <fa-icon icon="flag"></fa-icon>
                        </button>
                        <v-delete-button @delete="deleteProducts([product.id])" btn-class="btn-danger"/>
                    </b-td>
                </tr>
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
            go2PropertiesTab() {
                let tab = 'properties';
                Services.route().push({
                    tab: tab,
                    allTab: this.showAllTabs ? 1 : 0,
                }, location.pathname);
                this.$emit('changeTab', tab);
            },
            setData(data) {
                if (this.variantGroup.main_product_id !== data.variantGroup.main_product_id) {
                    location.reload();
                }
                this.variantGroup.id = data.variantGroup.id;
                this.variantGroup.name = data.variantGroup.name;
                this.variantGroup.main_product_id = data.variantGroup.main_product_id;
                this.variantGroup.updated_at = data.variantGroup.updated_at;
                this.variantGroup.products_count = data.variantGroup.products_count;
                this.products = data.products;
            },
            addProducts() {
                let newProductIds = Object.keys(this.newProducts);
                if (!newProductIds.length) {
                    Services.msg("Выберите товары", "danger");
                    return;
                }

                Services.showLoader();
                Services.net().post(this.getRoute('variantGroups.detail.products.add', {id: this.variantGroup.id}), {}, {
                    productIds: newProductIds,
                }).then((data) => {
                    this.setData(data);
                    this.newProducts = {};
                    Services.msg("Добавление товара(ов) прошло успешно");
                }, () => {
                    Services.msg("Ошибка при добавлении товара(ов) - возможно товар(ы) не имеет(ют) указанных характеристик для склейки, или есть дубли товаров по характеристикам для склейки", "danger");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            deleteProducts(productIds) {
                Services.showLoader();
                Services.net().delete(this.getRoute('variantGroups.detail.products.delete', {id: this.variantGroup.id}), {
                    productIds: productIds,
                }).then((data) => {
                    this.setData(data);
                    Services.msg("Удаление товара(ов) прошло успешно");
                }, () => {
                    Services.msg("Ошибка при удалении товара(ов)", "danger");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            setMainProduct(productId) {
                Services.showLoader();
                Services.net().put(
                    this.getRoute(
                        'variantGroups.detail.products.setMain',
                        {id: this.variantGroup.id, productId: productId},
                    ),
                ).then((data) => {
                    this.setData(data);
                    Services.msg("Изменение основного товара прошло успешно");
                }, () => {
                    Services.msg("Ошибка при изменении основного товара", "danger");
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
            exceptedIds() {
                let exceptedIds = [];
                this.products.forEach(product => {
                    exceptedIds.push(product.id);
                });

                return exceptedIds;
            }
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('variantGroups.detail.products.load', {id: this.model.id})).then(data => {
                this.setData(data);
            }).finally(() => {
                Services.hideLoader();
            });
        }
    }
</script>
<style>
    .property-color {
        border: 1px solid #000;
        border-radius: 10px;
        width: 20px;
        height: 20px;
        float: left;
        margin-right: 10px;
    }
</style>
