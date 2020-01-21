<template>
    <div class="shadow mt-3 p-3 w-100">
        <table class="table table-condensed">
            <thead>
            <tr>
                <th>Фото</th>
                <th class="with-small">Название <small>Артикул</small></th>
                <th></th>
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
                            <td><img :src="item.product.photo" class="preview" :alt="item.product.name"
                                     v-if="item.product.photo"></td>
                            <td class="with-small">
                                <a :href="getRoute('products.detail', {id: item.product.id})">
                                    {{ item.name }}
                                </a>
                                <small>{{ item.product.vendor_code }}</small>
                            </td>
                            <td>
                        <span class="segment" :class="segmentClass(item.product.segment)">
                            {{item.product.segment }}
                        </span>
                            </td>
                            <td class="with-small">
                                {{ item.product.category.name }}
                                <small>{{ item.product.brand.name }}</small>
                            </td>
                            <td>{{ item.qty | integer }}</td>
                            <td>{{ item.price }}</td>
                            <td>{{ item.discount }}</td>
                            <td>{{ item.cost }}</td>
                            <td>
                            </td>
                        </tr>
                    </template>
                </template>
                <template v-else>
                    <tr v-for="(item, key) in shipment.basketItems">
                        <td><img :src="item.product.photo" class="preview" :alt="item.product.name"
                                 v-if="item.product.photo"></td>
                        <td class="with-small">
                            <a :href="getRoute('products.detail', {id: item.product.id})">
                                {{ item.name }}
                            </a>
                            <small>{{ item.product.vendor_code }}</small>
                        </td>
                        <td>
                        <span class="segment" :class="segmentClass(item.product.segment)">
                            {{item.product.segment }}
                        </span>
                        </td>
                        <td class="with-small">
                            {{ item.product.category.name }}
                            <small>{{ item.product.brand.name }}</small>
                        </td>
                        <td>{{ item.qty | integer }}</td>
                        <td>{{ item.price }}</td>
                        <td>{{ item.discount }}</td>
                        <td>{{ item.cost }}</td>
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
import {mapGetters} from "vuex";

export default {
    data() {
        return {

        }
    },
    props: {
        shipments: {},
    },
    methods: {
        segmentClass(segment) {
            return segment ? `segment-${segment.toLowerCase()}` : '';
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
        ...mapGetters(['getRoute']),
    },
    mounted() {
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
    /* todo Вынести стили для сегментов в общий css-файл */
    .segment {
        position: relative;
        top: 5px;
        padding: 5px;
        border-radius: 50%;
        float: right;
        color: white;
        font-weight: bold;
        line-height: 20px;
        width: 32px;
        height: 32px;
        text-align: center;
    }
    .segment-a {
        background: #ffd700;
    }
    .segment-b {
        background: #c0c0c0;
    }
    .segment-c {
        background: #cd7f32;
    }
    .float-right {
        float: right;
    }
</style>
