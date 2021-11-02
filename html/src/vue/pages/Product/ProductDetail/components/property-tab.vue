<template>
    <div class="d-flex justify-content-start align-items-start">
        <div class="d-flex flex-column left-side">
            <shadow-card>
                <p v-if="product.archive" class="text-danger">{{ product.archive_comment }}</p>
                <button @click="openModal('ArchiveEdit')" class="btn btn-outline-warning btn-block mt-3" v-if="canUpdate(blocks.products)">
                    {{ product.archive ? 'Вернуть из архива' : 'Убрать в архив' }}
                </button>
            </shadow-card>
            <shadow-card title="Свойства товара" :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt'} : {}" @onEdit="openModal('productValuesEdit')">
                <table class="values-table">
                    <tbody>
                        <tr>
                            <th>Категория:</th>
                            <td>{{ categoryName }}</td>
                        </tr>
                        <tr>
                            <th>Бренд:</th>
                            <td>{{ brandName }}</td>
                        </tr>
                    </tbody>
                </table>
            </shadow-card>
            <shadow-card title="Шильдики на товаре"
                         :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt'} : {}"
                         @onEdit="openBadgesEditModal">
                <ul v-if="product_badges.length > 0">
                    <li v-for="badge in product_badges" v-if="options.availableBadges[badge]">
                        <h5>
                            <span class="badge badge-dark">
                                {{ options.availableBadges[badge].text }}
                            </span>
                        </h5>
                    </li>
                </ul>
                <button v-else-if="canUpdate(blocks.products)" @click="openBadgesEditModal"
                        class="btn btn-outline-dark btn-block mt-3">
                    Назначить шильдики
                </button>
            </shadow-card>
        </div>
        <shadow-card title="Характеристики" :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt'} : {}" @onEdit="openModal('productPropsEdit')" class="flex-grow-1">
            <table class="table table-striped table-bordered table-sm">
                <tbody>
                <tr v-for="property in options.availableProperties">
                    <td>{{ property.name }}</td>
                    <td>
                        <p v-for="value in values(property.id)" class="value">
                            <template v-if="property.type === 'directory'">{{ directoryValue(property.id, value) }}
                            </template>
                            <template v-else>{{value}}</template>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </shadow-card>

        <shadow-card title="Состав"
                     :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt'} : {}"
                     @onEdit="openModal('productIngredientsEdit')"
                     class="flex-grow-1">
            <p v-if="product_ingredients">
                {{ product_ingredients }}
            </p>
            <em v-else>
                (Состав не указан)
            </em>
        </shadow-card>

        <product-edit-modal
                :source="product"
                :options="options"
                @onSave="$emit('onSave')"
                modal-name="productValuesEdit"/>

        <archive-modal
                :source="product"
                @onSave="$emit('onSave')"
                modal-name="ArchiveEdit"/>

        <product-props-modal
                :product-id="product.id"
                :properties="propertyValues"
                :availableProperties="options.availableProperties"
                :directoryValues="options.directoryValues"
                @onSave="$emit('onSave')"
                modal-name="productPropsEdit"/>

        <product-ingredients-modal
            :product-id="product.id"
            :ingredients="product_ingredients"
            @onSave="$emit('onSave')"
            modal-name="productIngredientsEdit"/>

        <product-badges-modal
                :product-id="[product.id]"
                :available-badges="options.availableBadges"
                :attached-badges="product_badges"
                @save="updateBadges"/>
    </div>
</template>

<script>
import ShadowCard from '../../../../components/shadow-card.vue';
import ProductEditModal from './product-values-modal.vue';
import ProductPropsModal from './product-props-modal.vue';
import ProductIngredientsModal from './product-ingredients-modal.vue';
import ProductBadgesModal from './product-badges-modal.vue';
import ArchiveModal from './product-archive-modal.vue';

import modalMixin from '../../../../mixins/modal.js';

export default {
        components: {
            ShadowCard,
            ProductEditModal,
            ProductPropsModal,
            ProductIngredientsModal,
            ProductBadgesModal,
            ArchiveModal,
        },
        mixins: [modalMixin],
        props: {
            product: Object,
            brand: Object,
            category: Object,
            propertyValues: {},
            badges: Array,
            options: Object
        },
        data() {
            return {
                product_ingredients: this.composition(),
                product_badges: this.sortBadges(this.badges)
            }
        },
        methods: {
            updateBadges(badges) {
                this.product_badges = this.sortBadges(badges);
            },
            sortBadges(badgesToSort) {
                return Object.values(badgesToSort).sort((item1, item2) => {
                    if (this.options.availableBadges[item1]) {
                        if (this.options.availableBadges[item1].order_num
                            > this.options.availableBadges[item2].order_num) return 1;
                        if (this.options.availableBadges[item1].order_num
                            === this.options.availableBadges[item2].order_num) return 0;
                        if (this.options.availableBadges[item1].order_num
                            < this.options.availableBadges[item2].order_num) return -1;
                    }

                    return 0;
                })
            },
            openBadgesEditModal() {
                this.$bvModal.show('productBadgesEdit');
            },
            values(id) {
                return this.propertyValues[id] || [];
            },
            directoryValue(id, value) {
                let directory = this.options.directoryValues[id] || [];
                let result = directory.filter(d => d.id === value);
                return result.length > 0 ? result[0].name : undefined;
            },
            /**
             * Состав продукта
             * @returns {null|string}
             */
            composition() {
              return this.product.ingredients && this.product.ingredients.items ? this.product.ingredients.items.join(', ') : null;
            }
        },
        computed: {
            brandName() {
                let brand = Object.values(this.options.brands).find(brand => brand.id === this.product.brand_id);
                return brand ? brand.name : 'N/A';
            },
            categoryName() {
                let category = Object.values(this.options.categories).find(category => category.id ===
                    this.product.category_id);
                return category ? category.name : 'N/A';
            },
        }
    }
</script>

<style scoped>
    .left-side {
        max-width: 400px;
    }
    .value {
        margin: 0;
        padding: 0;
    }
    .values-table th {
        text-align: end;
        padding-right: 8px;
    }
    .values-table td {
        padding-left: 8px;
    }
</style>
