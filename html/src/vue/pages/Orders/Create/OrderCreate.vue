<template>
    <layout-main>
        <div class="select">
            <div class="row">
                <div class="col-4">
                    <f-input v-model="search.products" @change="productChange()" placeholder="e908-3543-9fdd,85de-3283-8b96">
                        Артикулы товаров (через ,)
                    </f-input>
                    Найденные товары:
                    <ul class="list-group mt-2">
                        <li class="list-group-item list-group-item-action" @click="selectProduct(product)" v-for="(product, key) in searchedProducts">
                            <small>{{ product.vendor_code }}</small> {{ product.name }}
                        </li>
                    </ul>
                </div>
                <div class="col-8">
                    Выбранные товары:
                    <ul class="list-group mt-2">
                        <li class="list-group-item list-group-item-action" v-for="(product, key) in selectedProducts">
                            <button class="btn btn-sm btn-dark" @click="changeProduct(product, false)">-</button>
                            <button class="btn btn-sm btn-dark" @click="changeProduct(product, true)">+</button>

                            <small class="ml-3">{{ product.vendor_code }}</small> {{ product.name }}
                            <span class="badge badge-primary badge-pill">{{ product.count }} шт.</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <f-input class="col-4" v-model="search.users" @change="customerChange()" :disabled="Object.keys(selectedProducts) < 1">
                    ID пользователя
                </f-input>
                <div class="col-8">
                    Пользователь:
                    {{ searchedUsers }}
                </div>
            </div>
            <div class="row">
                <f-input class="col-4" v-model="search.address" @change="addressChange()">
                    Адрес
                </f-input>
                <div class="col-8">
                    Пользователь:
                    selectedUser
                </div>
            </div>
            <div class="row">
                <f-select class="col-4" v-model="selectedCity" :options="avaliableCities">
                    Город
                </f-select>
                <div class="col-8">
                    Служба доставки:
                    selectedDelivery
                </div>
            </div>
        </div>

        <button class="btn btn-success" :disabled="checkOrder">Создать заказ</button>
    </layout-main>
</template>

<script>

import Services from "../../../../scripts/services/services";
import qs from 'qs';

import {mapGetters} from "vuex";

import FInput from '../../../components/filter/f-input.vue';
import FDate from '../../../components/filter/f-date.vue';
import FSelect from '../../../components/filter/f-select.vue';
import FMultiSelect from '../../../components/filter/f-multi-select.vue';
import Dropdown from '../../../components/dropdown/dropdown.vue';
import Helpers from "../../../../scripts/helpers";

import modalMixin from '../../../mixins/modal.js';


export default {
    name: 'page-order-create',
    mixins: [modalMixin],
    props: [
        'iOrders'
    ],
    components: {
        FInput,
        FDate,
        FSelect,
        FMultiSelect,
        Dropdown,
    },
    data() {
        return {
            search: {},
            address: '',
            searchedProducts: {},
            searchedUsers: {},
            selectedProducts: {},
            selectedUsers: {},
            selectedCity: '',
        }
    },
    methods: {
        checkOrder() {
            return false;
        },
        productChange() {
            Services.net().post(this.getRoute('orders.searchProducts', null), {}, {
                search: this.search.products
            })
                .then(result => {
                    this.searchedProducts = {};
                    if(result) {
                        this.searchedProducts = result;
                    }
                });
        },
        customerChange() {
            Services.net().post(this.getRoute('orders.searchUsers', null), {}, {
                search: this.search.users
            })
                .then(result => {
                    this.searchedUsers = {};
                    if(result) {
                        this.searchedUsers = result;
                    }
                });
        },
        selectProduct(product) {
            this.$set(this.selectedProducts, product.id, product);
            this.$set(this.selectedProducts[product.id], 'count', 1);
        },
        changeProduct(product, action) {
            if(!action && product.count === 1) {
                this.$delete(this.selectedProducts, product.id);
                return;
            }

            action ? product.count++ : product.count--;
            this.$set(this.selectedProducts, product.id, product);
        }
    },
    computed: {
        ...mapGetters(['getRoute']),

        avaliableCities() {
            return [
                {
                    value: 'c52ea942-555e-45c6-9751-58897717b02f',
                    text: 'Москва'
                },
                {
                    value: 'c52ea942-555e-45c6-9751-58897717b02f',
                    text: 'Санкт-петербург'
                },
                {
                    value: 'c52ea942-555e-45c6-9751-58897717b02f',
                    text: 'Казань'
                },
            ];
        },



    },
    created() {

    },
    watch: {
    }
};
</script>
<style scoped>
</style>
