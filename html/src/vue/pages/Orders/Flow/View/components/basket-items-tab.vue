<template>
    <div class="d-flex justify-content-start align-content-stretch">
        <div class="shadow mt-3 mr-3 p-3 w-100">
            <div class="d-flex justify-content-between mt-3 mb-3">
                <div class="action-bar d-flex justify-content-start">
                    <dropdown :items="dropdownItems" class="mr-4 order-btn">
                        <fa-icon icon="file-download"></fa-icon>
                        Скачать документы
                    </dropdown>
                    <dropdown :items="dropdownItems" class="mr-4 order-btn">
                        <fa-icon icon="print"></fa-icon>
                        Распечатать документы
                    </dropdown>
                    <div class="mr-4 order-btn">
                        <fa-icon icon="archive"></fa-icon>
                        Подбор товаров
                    </div>
                    <div class="mr-4 order-btn">
                        <fa-icon icon="check-circle"></fa-icon>
                        Сменить статус
                    </div>
                    <div class="mr-4 order-btn">
                        <fa-icon icon="truck"></fa-icon>
                        Сменить склад
                    </div>
                    <div class="mr-4 order-btn">
                        <fa-icon icon="comment-dots"></fa-icon>
                        Добавить комментарий
                    </div>
                </div>
            </div>
            <table class="table table-condensed">
                <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Фото</th>
                    <th class="with-small">Название <small>Артикул</small></th>
                    <th class="with-small">Категория <small>Бренд</small></th>
                    <th>Количество</th>
                    <th>Цена</th>
                    <th>Стоимость</th>
                    <th>Резерв</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="basketItem in basketItems">
                    <td>
                        <input type="checkbox" value="true" class="order-select" :value="basketItem.id">
                    </td>
                    <td>{{ basketItem.id }}</td>
                    <td><img :src="basketItem.product.photo" class="preview" :alt="basketItem.product.name"></td>
                    <td class="with-small">
                        <a :href="getRoute('product.edit', {id: basketItem.product.id})">{{ basketItem.name }}</a>
                        <small>{{ basketItem.product.vendor_code }}</small>
                        <span class="segment" :class="segmentClass(basketItem.segment)">{{ basketItem.product.segment }}</span>
                    </td>
                    <td class="with-small">
                        {{ basketItem.product.category.name }}
                        <small>{{ basketItem.product.brand.name }}</small>
                    </td>
                    <td>{{ basketItem.qty }}</td>
                    <td>{{ basketItem.price }}</td>
                    <td>{{ basketItem.cost }}</td>
                    <td class="with-small">
                        {{ basketItem.is_reserved ? 'Да' : 'Нет' }}
                        <small v-if="basketItem.is_reserved && basketItem.reserved_by.name">{{ basketItem.reserved_by.name }}</small>
                        <small v-if="basketItem.is_reserved && basketItem.reserved_at">{{ basketItem.reserved_at }}</small>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import {mapGetters} from "vuex";

import Dropdown from '../../../../../components/dropdown/dropdown.vue';

export default {
    props: [
        'basketItems',
    ],
    components: {
        Dropdown,
    },
    data() {
        return {
            dropdownItems: [
                {value: 1, text: 'Все'},
                {value: 2, text: 'Покупателю'},
                {value: 3, text: 'Курьеру'},
            ],
        };
    },
    methods: {
        segmentClass(segment) {
            return segment ? `segment-${segment.toLowerCase()}` : '';
        },
    },
    computed: {
        ...mapGetters(['getRoute']),
    },
};
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
        top: -32px;
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
</style>
