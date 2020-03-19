<template>
    <div>
        <form class="form-inline">
            <label class="mr-2" for="promo_page_name">Заголовок страницы</label>
            <input class="form-control" id="promo_page_name">
            <button class="btn btn-outline-success ml-2" @click="saveCustomer">Сохранить</button>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <td>ID</td>
                    <td>Изображение</td>
                    <td>Наименование товара</td>
                    <td>Категория</td>
                    <td>Бренд</td>
                    <td>Статус</td>
                    <td>Цена товара</td>
                    <td>Дата добавления</td>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in products">
                    <td>{{ product.id }}</td>
                    <td>
                        <img :src="product.image" width="150"/>
                    </td>
                    <td>{{ product.name }}</td>
                    <td>{{ product.category.name }}</td>
                    <td>{{ product.brand.name }}</td>
                    <td>Статус</td>
                    <td>{{ product.price }}</td>
                    <td>{{ product.created_at }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'tab-promo-page',
    props: ['model'],
    data() {
        return {
            products: [],
            form: {
                promo_page_name: this.model.promo_page_name,
            },
        }
    },
    methods: {
        savePromoProduct(promoProduct) {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.promoPage.add', {id: this.id}), promoProduct).then(data => {
                this.promoProducts = data.promoProducts;
                this.newPromoProduct.product_id = '';
                this.newPromoProduct.description = '';
                this.newPromoProduct.files = [];
            }).finally(() => {
                Services.hideLoader();
            })
        },
        saveCustomer() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.save', {id: this.customer.id}), {}, {
                customer: {
                    promo_page_name: this.form.promo_page_name,
                },
                activities: this.form.activities,
            }).then(data => {
                this.customer.promo_page_name = this.form.promo_page_name;
                Services.msg("Изменения сохранены");
            }).finally(() => {
                Services.hideLoader();
            })
        },
    },
    computed: {
        customer: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.promoPage', {id: this.model.id})).then(data => {
            this.products = data.products;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>