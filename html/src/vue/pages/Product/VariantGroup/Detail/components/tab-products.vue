<template>
    <div>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <div>
                <products-search :model="newProductId"></products-search>
                <button class="btn btn-success" v-b-modal.modal-add-variant-group>
                    <fa-icon icon="plus"></fa-icon> Добавить товар
                </button>
                <v-delete-button @delete="deleteProducts(selectedProducts)" btn-class="btn-danger"
                        v-if="selectedProducts.length > 0" class="ml-3"/>
            </div>
        </div>
        <b-table-simple hover small caption-top responsive>
            <b-thead>
                <b-tr>
                    <b-th></b-th>
                    <b-th>ID</b-th>
                    <b-th>Фото</b-th>
                    <b-th class="with-small">Название<br><small>Артикул</small></b-th>
                    <b-th>Дата создания</b-th>
                    <b-th class="with-small">Категория<br><small>Бренд</small></b-th>
                    <b-th class="with-small">Количество<br><small>Вес 1 шт</small><br><small>ДxШxВ 1 шт</small></b-th>
                    <b-th>Цена</b-th>
                    <b-th>Статус товара</b-th>
                    <b-th></b-th>
                </b-tr>
            </b-thead>
            <b-tbody>
                <template v-if="variantGroup.products.length > 0">
                    <tr v-for="product in variantGroup.products">
                        <b-td>
                            <input type="checkbox" value="true" :value="product.id" v-model="selectedProducts">
                        </b-td>
                        <b-td>{{ product.id }}</b-td>
                        <b-td><img :src="productPhoto(product)" class="preview" :alt="product.name" v-if="product.mainImage"></b-td>
                        <b-td class="with-small">
                            <a :href="getRoute('products.detail', {id: product.id})" target="_blank">
                                {{ product.name }}
                            </a><br>
                            <small>{{ product.vendor_code }}</small>
                        </b-td>
                        <b-td>{{ product.created_at }}</b-td>
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
                newProductId: 0,
                selectedProducts: [],
            }
        },
        methods: {
            productPhoto(product) {
                return '/files/compressed/' + product.mainImage.file_id + '/50/50/webp';
            },
            deleteProducts(ids) {
                Services.showLoader();
                Services.net().delete(this.getRoute('variantGroups.detail.products.delete'), {
                    ids: ids,
                }).then((data) => {
                    this.variantGroup = data.variantGroup;
                    Services.msg("Удаление прошло успешно");
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
        }
    }
</script>
