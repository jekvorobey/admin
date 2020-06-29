<template>
    <div class="d-flex justify-content-start align-items-start">
        <div class="d-flex flex-column left-side">
            <shadow-card>
                <p v-if="product.archive" class="text-danger">{{ product.archive_comment }}</p>
                <button @click="openModal('ArchiveEdit')" class="btn btn-outline-warning btn-block mt-3">
                    {{ product.archive ? 'Вернуть из архива' : 'Убрать в архив' }}
                </button>
            </shadow-card>
            <shadow-card title="Свойства товара" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('productValuesEdit')">
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
                        <tr>
                            <th>Новинка:</th>
                            <td>{{ product.is_new ? 'Да' : 'Нет' }}</td>
                        </tr>
                    </tbody>
                </table>
            </shadow-card>
            <shadow-card title="Шильдики на товаре"
                         :buttons="{onEdit:'pencil-alt'}"
                         @onEdit="openBadgesEditModal">
                <ul v-if="product_badges.length > 0">
                    <li v-for="badge in product_badges">
                        <h5>
                            <span class="badge"
                                  :class="badgeClass(options.availableBadges[badge].type)">
                                {{ options.availableBadges[badge].text }}
                            </span>
                        </h5>
                    </li>
                </ul>
                <button v-else @click="openBadgesEditModal"
                        class="btn btn-outline-dark btn-block mt-3">
                    Назначить шильдики
                </button>
            </shadow-card>
        </div>
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

        <archive-modal
                :source="product"
                @onSave="$emit('onSave')"
                modal-name="ArchiveEdit">
        </archive-modal>

        <product-props-modal
                :product-id="product.id"
                :properties="propertyValues"
                :availableProperties="options.availableProperties"
                :directoryValues="options.directoryValues"
                @onSave="$emit('onSave')"
                modal-name="productPropsEdit">
        </product-props-modal>

        <product-badges-modal
                :product-id="[product.id]"
                :available-badges="options.availableBadges"
                @save="updateBadges"/>
    </div>
</template>

<script>
    import ShadowCard from '../../../../components/shadow-card.vue';
    import ProductEditModal from './product-values-modal.vue';
    import ProductPropsModal from './product-props-modal.vue';
    import ProductBadgesModal from './product-badges-modal.vue';
    import ArchiveModal from './product-archive-modal.vue';

    import modalMixin from '../../../../mixins/modal.js';

    export default {
        components: {
            ShadowCard,
            ProductEditModal,
            ProductPropsModal,
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
                product_badges: this.sortBadges(this.badges)
            }
        },
        methods: {
            updateBadges(badges) {
                this.product_badges = this.sortBadges(badges);
            },
            sortBadges(badgesToSort) {
                return Object.values(badgesToSort).sort((item1, item2) => {
                    if (this.options.availableBadges[item1].order_num
                        > this.options.availableBadges[item2].order_num) return 1;
                    if (this.options.availableBadges[item1].order_num
                        === this.options.availableBadges[item2].order_num) return 0;
                    if (this.options.availableBadges[item1].order_num
                        < this.options.availableBadges[item2].order_num) return -1;
                })
            },
            openBadgesEditModal() {
                this.$bvModal.show('productBadgesEdit');
            },
            badgeClass(type) {
                switch (type) {
                    case 1: return 'badge-dark';
                    case 2: return 'badge-warning';
                    case 3: return 'badge-secondary';
                    default: return 'badge-light';
                }
            },
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
                let brand = Object.values(this.options.brands).find(brand => brand.id === this.product.brand_id);
                return brand ? brand.name : 'N/A';
            },
            categoryName() {
                let category = Object.values(this.options.categories).find(category => category.id ===
                    this.product.category_id);
                return category ? category.name : 'N/A';
            }
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