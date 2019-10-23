<template>
    <div class="d-flex justify-content-start align-items-start">
        <shadow-card title="Свойства товара" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('productValuesEdit')">
            <table class="values-table">
                <tbody>
                <tr>
                    <th>Сегмент:</th>
                    <td>{{ product.segment }}</td>
                </tr>
                <tr>
                    <th>Категория:</th>
                    <td>{{ categoryName }}</td>
                </tr>
                <tr>
                    <th>Бренд:</th>
                    <td>{{ brandName }}</td>
                </tr>
                <tr>
                    <th>Ширина:</th>
                    <td>{{ product.width }} мм</td>
                </tr>
                <tr>
                    <th>Высота:</th>
                    <td>{{ product.height }} мм</td>
                </tr>
                <tr>
                    <th>Глубина:</th>
                    <td>{{ product.length }} мм</td>
                </tr>
                <tr>
                    <th>Вес:</th>
                    <td>{{ product.weight }} гр</td>
                </tr>
                </tbody>
            </table>
        </shadow-card>
        <shadow-card title="Характеристики" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('productPropsEdit')" class="flex-grow-1">
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

        <product-edit-modal
                :source="product"
                :options="options"
                @onSave="$emit('onSave')"
                modal-name="productValuesEdit">
        </product-edit-modal>

        <product-props-modal
                :properties="propertyValues"
                :availableProperties="options.availableProperties"
                :directoryValues="options.directoryValues"
                modal-name="productPropsEdit">
        </product-props-modal>
    </div>
</template>

<script>
    import ShadowCard from '../../../../components/shadow-card.vue';
    import ProductEditModal from './product-values-modal.vue';
    import ProductPropsModal from './product-props-modal.vue';

    import modalMixin from '../../../../mixins/modal.js';

    export default {
        components: {
            ShadowCard,
            ProductEditModal,
            ProductPropsModal,
        },
        mixins: [modalMixin],
        props: {
            product: Object,
            brand: Object,
            category: Object,
            propertyValues: {},

            options: Object
        },
        methods: {
            values(id) {
                return this.propertyValues[id] || [];
            },
            directoryValue(id, value) {
                let directory = this.options.directoryValues[id] || [];
                let result = directory.filter(d => d.id === value);
                return result.length > 0 ? result[0].name : undefined;
            }
        },
        computed: {
            brandName() {
                let brand = this.options.brands.find(brand => brand.id === this.product.brand_id);
                return brand ? brand.name : 'N/A';
            },
            categoryName() {
                let category = this.options.categories.find(category => category.id === this.product.category_id);
                return category ? category.name : 'N/A';
            }
        }
    }
</script>

<style scoped>
    .value {
        margin: 0;
        padding: 0;
    }
    .card-head {
        height: 48px;
        padding: 8px 16px;
    }
    .corner-edit-btn {
        position: relative;
        float: right;
        top: 5px;
        color: gray;
        transition: 0.3s all;
        cursor: pointer;
    }
    .corner-edit-btn:hover {
        color: black;
    }
    .values-table th {
        text-align: end;
        padding-right: 8px;
    }
    .values-table td {
        padding-left: 8px;
    }
</style>