<template>
    <table class="table table-sm">
        <thead>
        <tr class="table-secondary">
            <th colspan="2">
                Личные предпочтения
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>
                Бренды
                <button class="btn btn-success btn-sm" @click="editBrands(1)"><fa-icon icon="pencil-alt"/></button>
            </th>
            <td>
                <div v-for="brand_id in pref_personal.brands">{{ brands[brand_id].name }}</div>
                <div v-if="!pref_personal.brands.length">-</div>

                <modal-brands v-if="type===1" :model.sync="pref_personal.brands" :brands="brands" :customer-id="id" :type="type"/>
            </td>
        </tr>
        <tr>
            <th>
                Категории
                <button class="btn btn-success btn-sm" @click="editCategories(1)"><fa-icon icon="pencil-alt"/></button>
            </th>
            <td>
                <div v-for="category_id in pref_personal.categories">{{ categoryName(category_id) }}</div>
                <div v-if="!pref_personal.categories.length">-</div>

                <modal-categories v-if="type===1" :model.sync="pref_personal.categories" :categories="categories" :customer-id="id" :type="type"/>
            </td>
        </tr>

        <tr class="table-secondary">
            <th colspan="2">Профессиональные предпочтения</th>
        </tr>
        <tr>
            <th>
                Бренды
                <button class="btn btn-success btn-sm" @click="editBrands(2)"><fa-icon icon="pencil-alt"/></button>
            </th>
            <td>
                <div v-for="brand_id in pref_referral.brands">{{ brands[brand_id].name }}</div>
                <div v-if="!pref_referral.brands.length">-</div>

                <modal-brands v-if="type===2" :model.sync="pref_referral.brands" :brands="brands" :customer-id="id" :type="type"/>
            </td>
        </tr>
        <tr>
            <th>
                Категории
                <button class="btn btn-success btn-sm" @click="editCategories(2)"><fa-icon icon="pencil-alt"/></button>
            </th>
            <td>
                <div v-for="category_id in pref_referral.categories">{{ categoryName(category_id) }}</div>
                <div v-if="!pref_referral.categories.length">-</div>

                <modal-categories v-if="type===2" :model.sync="pref_referral.categories" :categories="categories" :customer-id="id" :type="type"/>
            </td>
        </tr>

        <tr class="table-secondary">
            <th colspan="2">Избранное пользователя</th>
        </tr>
        <tr>
            <th>
                Избранное
                <button class="btn btn-success btn-sm" v-b-modal.modal-find-product><fa-icon icon="plus"/></button>
                <modal-find-product v-bind:id="id"/>
            </th>
            <td>
                <div v-for="(favorite_item, index) in favorites">
                    <a :href="'/products/' + favorite_item.id">
                        {{ favorite_item.name }}
                    </a>
                    <span @click="removeFavItem(id, favorite_item.id, index)">
                        <fa-icon icon="times"/>
                    </span>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import ModalBrands from './modal-brands.vue';
import ModalCategories from './modal-categories.vue';
import ModalFindProduct from './modal-find-product.vue';

export default {
    name: 'tab-preference',
    components: {ModalCategories, ModalBrands, ModalFindProduct},
    props: ['id'],
    data() {
        return {
            type: null,
            brands: [],
            categories: [],
            pref_personal: {
                brands: [],
                categories: [],
            },
            pref_referral: {
                brands: [],
                categories: [],
            },
            favorites: [],
        };
    },
    methods: {
        editBrands: async function (type) {
            switch (type) {
                case 1:
                    this.type = 1;
                    await this.$nextTick();
                    this.$bvModal.show('modal-brands');
                    break;
                case 2:
                    this.type = 2;
                    await this.$nextTick();
                    this.$bvModal.show('modal-brands');
                    break;
                default:
                    this.type = null;
            }
        },
        editCategories: async function (type) {
            switch (type) {
                case 1:
                    this.type = 1;
                    await this.$nextTick();
                    this.$bvModal.show('modal-categories');
                    break;
                case 2:
                    this.type = 2;
                    await this.$nextTick();
                    this.$bvModal.show('modal-categories');
                    break;
                default:
                    this.type = null;
            }
        },
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
        removeFavItem(id, itemId, index) {
            Services.showLoader();
            Services.net().delete(this.getRoute('customers.detail.preference.favorite.delete', {id: id, product_id: itemId})).then(data => {
                this.favorites.splice(index, 1);
            }).finally(() => {
                Services.hideLoader();
            });
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.preference', {id: this.id})).then(data => {
            this.brands = data.brands;
            this.categories = data.categories;
            this.pref_personal.brands = data.pref_personal.brands;
            this.pref_personal.categories = data.pref_personal.categories;
            this.pref_referral.brands = data.pref_referral.brands;
            this.pref_referral.categories = data.pref_referral.categories;
            this.favorites = data.favorites;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>
