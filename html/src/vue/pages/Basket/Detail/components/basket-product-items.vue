<template>
    <b-card>
        <b-table-simple hover small caption-top responsive="true">
            <b-thead>
                <b-tr>
                    <b-th>Фото</b-th>
                    <b-th class="with-small">Название <small>ID</small><small>Артикул</small></b-th>
                    <b-th class="with-small">Категория <small>Бренд</small></b-th>
                    <b-th class="with-small">Количество <small>Вес 1 шт</small><small>ДxШxВ 1 шт</small></b-th>
                    <b-th>Цена за единицу со скидкой
                        <fa-icon icon="question-circle"
                                 v-b-popover.hover="tooltipUnitPriceHelp"></fa-icon>
                    </b-th>
                </b-tr>
            </b-thead>
            <b-tbody>
                <template>
                    <tr v-for="basketItem in basket.items">
                        <b-td><img :src="imageUrl(basketItem.product.mainImage.file_id, 100, 100)" class="preview" :alt="basketItem.name"
                                   v-if="basketItem.product && basketItem.product.mainImage"></b-td>
                        <b-td class="with-small">
                            <a :href="getRoute('products.detail', {id: basketItem.product.id})" target="_blank">
                                {{ basketItem.name }}
                            </a>
                            <small>{{ basketItem.product.id }}</small>
                            <small>{{ basketItem.product ? basketItem.product.vendor_code : '' }}</small>
                            <fa-icon class="cursor-pointer" icon="link" @click="showCase(basketItem.product.code)"></fa-icon>
                        </b-td>
                        <b-td class="with-small">
                            {{
                                basketItem.product && basketItem.product.category ? basketItem.product.category.name : ''
                            }}
                            <small>{{
                                    basketItem.product && basketItem.product.brand ? basketItem.product.brand.name : ''
                                }}</small>
                        </b-td>
                        <b-td class="with-small">
                            {{ basketItem.qty | integer }} шт.
                            <small> {{ basketItem.product.weight }} г</small>
                            <small> {{ basketItem.product.length }} x {{ basketItem.product.width }} x
                                {{ basketItem.product.height }} мм</small>
                        </b-td>
                        <b-td>{{ preparePrice(basketItem.price / basketItem.qty) }} руб.</b-td>
                    </tr>
                </template>
            </b-tbody>
        </b-table-simple>

    </b-card>
</template>

<script>
    import mediaMixin from '../../../../mixins/media';
    import showCaseMixin from '../../../../mixins/show-case';

    export default {
        name: "basket-product-items",
        mixins: [
            mediaMixin,
            showCaseMixin,
        ],
        components: {
        },
        props: {
            model: {
                type: Object,
            },
        },
        data() {
            return {

            }
        },
        methods: {

        },
        computed: {
            basket: {
                get() {
                    return this.model
                },
                set(value) {
                    this.$emit('update:modelBasket', value)
                },
            },
            tooltipUnitPriceHelp() {
                return 'Цена товара со всеми скидками за единицу товара';
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
