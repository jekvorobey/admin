<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="2">
                Личные предпочтения
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>
                Бренды
                <button class="btn btn-success btn-sm" v-b-modal.modal-brands><fa-icon icon="pencil-alt"/></button>
            </th>
            <td>
                <div v-for="brand_id in customer.brands">{{ brands[brand_id].name }}</div>
                <div v-if="!customer.brands.length">-</div>

                <modal-brands :model.sync="customer.brands" :brands="brands" :customer-id="id"/>
            </td>
        </tr>
        <tr>
            <th>
                Категории
                <button class="btn btn-success btn-sm" v-b-modal.modal-categories><fa-icon icon="pencil-alt"/></button>
            </th>
            <td>
                <div v-for="category_id in customer.categories">{{ categoryName(category_id) }}</div>
                <div v-if="!customer.categories.length">-</div>

                <modal-categories :model.sync="customer.categories" :categories="categories" :customer-id="id"/>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import ModalBrands from './modal-brands.vue';
import ModalCategories from './modal-categories.vue';

export default {
    name: 'info-preference',
    components: {ModalCategories, ModalBrands},
    props: ['id'],
    data() {
        return {
            brands: [],
            categories: [],
            customer: {
                brands: [],
                categories: [],
            },
        };
    },
    methods: {
        categoryName(category_id) {
            let name = [];
            let parent_id = category_id;
            while (parent_id) {
                let category = this.categories[parent_id];
                parent_id = category.parent_id;
                name.unshift(category.name);
            }

            return name.join(': ');
        },
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.preference', {id: this.id})).then(data => {
            this.brands = data.brands;
            this.categories = data.categories;
            this.customer.brands = data.customer.brands;
            this.customer.categories = data.customer.categories;
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>