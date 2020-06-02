<template>
    <b-card>
        <b-table-simple hover small caption-top responsive>
            <b-thead>
                <b-tr>
                    <b-th>Фото</b-th>
                    <b-th class="with-small">Название <small>Артикул</small></b-th>
                    <b-th class="with-small">Категория <small>Бренд</small></b-th>
                    <b-th>Количество</b-th>
                    <b-th>Цена без скидки</b-th>
                    <b-th>Скидка</b-th>
                    <b-th>Цена со скидкой</b-th>
                    <b-th v-if="canEdit"></b-th>
                </b-tr>
            </b-thead>
            <b-tbody>
                <template v-if="shipment.packages.length > 0">
                    <template v-for="(pack, key) in shipment.packages">
                        <tr>
                            <b-td colspan="7">
                                Коробка #{{ key+1 }}
                            </b-td>
                            <b-td v-if="canEdit">
                                <fa-icon icon="pencil-alt" title="Изменить" class="cursor-pointer mr-3"
                                         @click="editPackage(item.id)"
                                />
                                <fa-icon icon="times" title="Удалить" class="cursor-pointer"
                                         @click="deletePackage(item.id)"
                                />
                            </b-td>
                        </tr>
                        <tr v-for="(item, key) in pack.items">
                            <b-td><img :src="productPhoto(item.basketItem.product)" class="preview" :alt="item.name"
                                       v-if="item.basketItem.product.mainImage"></b-td>
                            <b-td class="with-small">
                                <a :href="getRoute('products.detail', {id: item.basketItem.product.id})" target="_blank">
                                    {{ item.basketItem.name }}
                                </a>
                                <small>{{ item.basketItem.product.vendor_code }}</small>
                            </b-td>
                            <b-td class="with-small">
                                {{ item.basketItem.product && item.basketItem.product.category ? item.basketItem.product.category.name : '' }}
                                <small>{{ item.basketItem.product && item.basketItem.product.category ? item.basketItem.product.brand.name : '' }}</small>
                            </b-td>
                            <b-td>{{ item.qty | integer }}</b-td>
                            <b-td>{{ preparePrice(item.cost)}} руб.</b-td>
                            <b-td>{{ preparePrice(item.cost - item.price) }} руб.</b-td>
                            <b-td>{{ preparePrice(item.price) }} руб.</b-td>
                            <b-td v-if="canEdit"></b-td>
                        </tr>
                    </template>
                </template>
                <template v-if="shipment.nonPackedBasketItems">
                    <tr v-for="(item, key) in shipment.nonPackedBasketItems">
                        <b-td><img :src="productPhoto(item.product)" class="preview" :alt="item.name" v-if="item.product.mainImage"></b-td>
                        <b-td class="with-small">
                            <a :href="getRoute('products.detail', {id: item.product.id})" target="_blank">
                                {{ item.name }}
                            </a>
                            <small>{{ item.product.vendor_code }}</small>
                        </b-td>
                        <b-td class="with-small">
                            {{ item.product && item.product.category ? item.product.category.name : ''}}
                            <small>{{ item.product && item.product.brand ? item.product.brand.name : ''}}</small>
                        </b-td>
                        <b-td>{{ item.qty | integer }}</b-td>
                        <b-td>{{ preparePrice(item.cost) }} руб.</b-td>
                        <b-td>{{ preparePrice((item.cost - item.price)) }} руб.</b-td>
                        <b-td>{{ preparePrice(item.price) }} руб.</b-td>
                        <b-td v-if="canEdit"></b-td>
                    </tr>
                </template>
            </b-tbody>
        </b-table-simple>
    </b-card>
</template>

<script>
    export default {
        name: "shipment-items",
        props: {
            modelShipment: {
                type: Object,
            },
            modelOrder: {
                type: Object,
            },
            canEdit: {
                type: Boolean,
                default: false,
            }
        },
        methods: {
            productPhoto(product) {
                return '/files/compressed/' + product.mainImage.file_id + '/50/50/webp';
            },
        },
        computed: {
            order: {
                get() {
                    return this.modelOrder
                },
                set(value) {
                    this.$emit('update:modelOrder', value)
                },
            },
            shipment: {
                get() {
                    return this.modelShipment
                },
                set(value) {
                    this.$emit('update:modelShipment', value)
                },
            },
        }
    }
</script>

<style scoped>
    .with-small small {
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