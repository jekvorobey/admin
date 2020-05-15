<template>
    <div>
            <div class="form-inline mb-2">
                    <label class="mr-2" for="promo_page_name">Заголовок страницы</label>
                    <input class="form-control" id="promo_page_name" v-model="form.promo_page_name">
                    <button class="btn btn-outline-success ml-2" @click="saveCustomer">Сохранить</button>
                    <label class="mx-2" for="product_id">Добавить товар по ID</label>
                    <v-input id="product_id"
                             type="number"
                             v-model="form.product_id"
                             :error="productIdError"
                             placeholder="ID товара"
                             aria-required="true"/>
                    <button class="btn btn-outline-success ml-2" @click="savePromoProduct">Добавить</button>
            </div>

        <div class="mb-2">
            Ссылка на промостраницу: <a :href="url">{{ url }}</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Изображение</th>
                    <th>Наименование товара</th>
                    <th>Категория</th>
                    <th>Бренд</th>
                    <th>Статус</th>
                    <th>Цена товара</th>
                    <th>Дата добавления</th>
                    <th></th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th><input v-model="filter.product" class="form-control form-control-sm"></th>
                    <th>
                        <select v-model="filter.category" class="form-control form-control-sm">
                            <option :value="null">Все</option>
                            <option v-for="category in categories" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </th>
                    <th>
                        <select v-model="filter.brand" class="form-control form-control-sm">
                            <option :value="null">Все</option>
                            <option v-for="brand in brands" :value="brand.id">
                                {{ brand.name }}
                            </option>
                        </select>
                    </th>
                    <th></th>
                    <th></th>
                    <th>
                        <date-picker v-model="filter.date" value-type="format" format="YYYY-MM-DD" range input-class="form-control form-control-sm"/>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in filterProducts">
                    <td>{{ product.id }}</td>
                    <td>
                        <img v-if="product.image" :src="media.file(product.image)" width="150"/>
                    </td>
                    <td><a :href="getRoute('products.detail', {id: product.id})">{{ product.name }}</a></td>
                    <td>{{ product.category.name }}</td>
                    <td>{{ product.brand.name }}</td>
                    <td>{{ product.status }}</td>
                    <td>{{ product.price }}</td>
                    <td>{{ product.created_at }}</td>
                    <td>
                        <v-delete-button btn-class="btn-danger btn-sm" @delete="deletePromoProduct(product)"/>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import NetService from '../../../../../scripts/services/net.js';
import DatePicker from 'vue2-datepicker';
import VInput from "../../../../components/controls/VInput/VInput.vue";
import 'vue2-datepicker/index.css';
import 'vue2-datepicker/locale/ru.js';
import moment from 'moment';

import {validationMixin} from 'vuelidate';
import {required, integer} from 'vuelidate/lib/validators';

export default {
    name: 'tab-promo-page',
    components: {VInput, VDeleteButton, DatePicker},
    mixins: [validationMixin],
    props: ['model'],
    data() {
        return {
            products: [],
            brands: [],
            categories: [],
            url: '',
            form: {
                promo_page_name: this.model.promo_page_name,
                product_id: '',
            },
            filter: {
                product_name: '',
                category: null,
                brand: null,
                date: [
                    null,
                    null,
                ],
            }
        }
    },
    validations: {
        form: {
            product_id: {required, integer},
        },
    },
    methods: {
        savePromoProduct() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }
            Services.showLoader();
            Services.net().post(this.getRoute('customers.detail.promoPage.add', {id: this.model.id}), {
                product_id: this.form.product_id
            }).then(data => {
                this.products = data.products;
                this.brands = data.brands;
                this.categories = data.categories;
                this.form.product_id = '';
            }).finally(() => {
                this.$v.$reset();
                Services.hideLoader();
            })
        },
        deletePromoProduct(product) {
            Services.showLoader();
            Services.net().delete(this.getRoute('customers.detail.promoPage.delete', {id: this.model.id}), {
                product_id: product.id
            }).then(data => {
                this.products = data.products;
                this.brands = data.brands;
                this.categories = data.categories;
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
        productIdError() {
            if (this.$v.form.product_id.$dirty) {
                if (!this.$v.form.product_id.required) {
                    return 'Введите ID товара!'
                }
                if (!this.$v.form.product_id.integer) {
                    return "Введите целое число!";
                }
            }
        },
        filterProducts() {
            return this.products.filter(product => {
                if (this.filter.product) {
                    if (product.name.toLowerCase().indexOf(this.filter.product.toLowerCase()) === -1) {
                        return false;
                    }
                }

                if (this.filter.brand) {
                    if (product.brand.id !== this.filter.brand) {
                        return false;
                    }
                }

                if (this.filter.category) {
                    if (product.category.id !== this.filter.category) {
                        return false;
                    }
                }

                if (this.filter.date[0] && this.filter.date[1]) {
                    let created_at = moment(product.created_at);
                    let date_from = moment(this.filter.date[0]);
                    let date_to = moment(this.filter.date[1]).add(1, 'day');
                    if (!(date_from <= created_at && created_at <= date_to)) {
                        return false;
                    }
                }

                return true;
            });
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.promoPage', {id: this.model.id})).then(data => {
            this.products = data.products;
            this.brands = data.brands;
            this.categories = data.categories;
            this.url = NetService.prepareUri(data.url, {code: this.model.referral_code}).uri;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>