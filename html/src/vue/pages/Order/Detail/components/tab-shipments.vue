<template>
    <div class="shadow mt-3 p-3 w-100">
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Фото</th>
                    <th class="with-small">Название <small>Артикул</small></th>
                    <th class="with-small">Категория <small>Бренд</small></th>
                    <th>Количество</th>
                    <th>Цена без скидки</th>
                    <th>Скидка</th>
                    <th>Цена со скидкой</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <template v-for="(shipment, key) in shipments">
                <tr>
                    <td colspan="8">
                        <b>Отправление {{ shipment.number }}</b>
                    </td>
                    <td>
                        <fa-icon icon="pencil-alt" title="Изменить" class="cursor-pointer mr-3"
                                 @click="editShipment(item.id)"
                        />
                        <fa-icon icon="times" title="Удалить" class="cursor-pointer"
                                 @click="deleteShipment(item.id)"
                        />
                    </td>
                </tr>
                <template v-if="shipment.packages.length > 0">
                    <template v-for="(pack, key) in shipment.packages">
                        <tr>
                            <td colspan="8">
                                Коробка #{{ key+1 }}
                            </td>
                            <td>
                                <fa-icon icon="pencil-alt" title="Изменить" class="cursor-pointer mr-3"
                                     @click="editPackage(item.id)"
                                />
                                <fa-icon icon="times" title="Удалить" class="cursor-pointer"
                                     @click="deletePackage(item.id)"
                                />
                            </td>
                        </tr>
                        <tr v-for="(item, key) in pack.items">
                            <td><img :src="productPhoto(item.basketItem.product)" class="preview" :alt="item.name"
                                     v-if="item.basketItem.product.mainImage"></td>
                            <td class="with-small">
                                <a :href="getRoute('products.detail', {id: item.basketItem.product.id})" target="_blank">
                                    {{ item.basketItem.name }}
                                </a>
                                <small>{{ item.basketItem.product.vendor_code }}</small>
                            </td>
                            <td class="with-small">
                                {{ item.basketItem.product && item.basketItem.product.category ? item.basketItem.product.category.name : '' }}
                                <small>{{ item.basketItem.product && item.basketItem.product.category ? item.basketItem.product.brand.name : '' }}</small>
                            </td>
                            <td>{{ item.qty | integer }}</td>
                            <td>{{ item.cost}}</td>
                            <td>{{ (item.cost - item.price) }}</td>
                            <td>{{ item.price }}</td>
                            <td>
                            </td>
                        </tr>
                    </template>
                </template>
                <template v-else>
                    <tr v-for="(item, key) in shipment.basketItems">
                        <td><img :src="productPhoto(item.product)" class="preview" :alt="item.name" v-if="item.product.mainImage"></td>
                        <td class="with-small">
                            <a :href="getRoute('products.detail', {id: item.product.id})" target="_blank">
                                {{ item.name }}
                            </a>
                            <small>{{ item.product.vendor_code }}</small>
                        </td>
                        <td class="with-small">
                            {{ item.product && item.product.category ? item.product.category.name : ''}}
                            <small>{{ item.product && item.product.brand ? item.product.brand.name : ''}}</small>
                        </td>
                        <td>{{ item.qty | integer }}</td>
                        <td>{{ roundValue(item.cost) }} руб.</td>
                        <td>{{ roundValue((item.cost - item.price)) }} руб.</td>
                        <td>{{ roundValue(item.price) }} руб.</td>
                        <td>
                        </td>
                    </tr>
                </template>
            </template>
            </tbody>
        </table>
    </div>
</template>
<script>

    import Helpers from "../../../../../scripts/helpers";

    export default {
    data() {
        return {

        }
    },
    props: {
        model: {},
    },
    methods: {
        productPhoto(product) {
            return '/files/compressed/' + product.mainImage.file_id + '/50/50/webp';
        },
        roundValue(value) {
            return Helpers.roundValue(value)
        },
        editShipment(id) {

        },
        deleteShipment(id) {

        },
        editProduct(id) {

        },
        deleteProduct(id) {

        }
    },
    computed: {
        shipments: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
    }
}
</script>

<style scoped>
    th {
        vertical-align: top !important;
    }
    .with-small small{
        display: block;
        color: gray;
        line-height: 1rem;
        overflow: hidden;
    }
    .preview {
        height: 50px;
        border-radius: 5px;
    }
</style>
