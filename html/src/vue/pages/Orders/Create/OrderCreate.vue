<template>
    <layout-main>
        <div class="select">
            <div class="row">
                <div class="col-12">
                    <f-input v-model="search.products" @change="productChange()" placeholder="e908-3543-9fdd,85de-3283-8b96">
                        Артикулы товаров (через ,)
                    </f-input>
                    Найденные товары:
                    <ul class="list-group mt-2">
                        <li class="list-group-item list-group-item-action" @click="selectProduct(product)" v-for="(product, key) in searchedProducts">
                            <img :src="product.photo" class="preview" :alt="product.name"
                                 v-if="product.photo">
                            {{ product.name }}
                            <small>{{ product.vendor_code }}</small>
                        </li>
                    </ul>
                </div>
                <div class="col-12">
                    Выбранные товары:
                    <ul class="list-group mt-2">
                        <li class="list-group-item list-group-item-action" v-for="(product, key) in selectedProducts">
                            <button class="btn btn-sm btn-dark" @click="changeProduct(product, false)">-</button>
                            <button class="btn btn-sm btn-dark" @click="changeProduct(product, true)">+</button>
                            <img :src="product.photo" class="preview" :alt="product.name"
                                 v-if="product.photo">
                            {{ product.name }}
                            <small class="ml-3">{{ product.vendor_code }}</small>
                            <span class="badge badge-primary badge-pill">{{ product.count }} шт.</span>
                            <div v-for="(offer, key) in product.offers" :key="offer.id">
                                <input :id="'check' + offer.id" type="radio" v-model="selectedOffers[product.id]" :value="offer.id">
                                <label :for="'check' + offer.id">
                                    Мерчант: | Склад:
                                    <span class="badge badge-secondary badge-pill">{{ saleStatusName(offer.sale_status) }}</span>
                                </label>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row mt-5">
                <f-select class="col-1" v-model="selectedUserType" :options="userTypeSelect" without_none>
                    Пользователь
                </f-select>
                <f-input class="col-3" v-model="search.users" @change="customerChange()" :disabled="Object.keys(selectedOffers) < 1">
                    &nbsp;
                </f-input>
                <div class="col-8">
                    Выбр:
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

        <button class="btn btn-success" :disabled="checkOrder" @click="createOrder">Создать заказ</button>
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
        'iOrders',
        'offerSaleStatuses',
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
            searchedStocks: {},
            selectedOffers: {},
            selectedUser: {},
            selectedCity: '',
            selectedUserType: 'fio'
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
                    this.searchedStocks = {};
                    if(result) {
                        this.searchedProducts = result.products;
                        this.searchedStocks = result.stocks;
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
        createOrder() {
            Services.net().post(this.getRoute('orders.createOrder', null), {}, {
                customer_id: this.search.users,

            })
                .then(result => {
                    if(result) {

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
        },
        changeOffer(offer) {

        },
        saleStatusName(id) {
            let status = this.offerSaleStatuses[id];
            return status ? status.name : 'N/A';
        },
        formatIds(ids) {
            if (!ids) {
                return [];
            }

            return ids
                .split(',')
                .map(id => { return parseInt(id); })
                .filter(id => { return id > 0 });
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
        userTypeSelect() {
            return [
                {
                    value: 'fio',
                    text: 'ФИО'
                },
                {
                    value: 'id',
                    text: 'ID'
                },
            ];
        },



    },
    created() {

    },
    watch: {
        'search.users': {
            handler(val, oldVal) {
                if (val && val !== oldVal) {
                    let format = this.formatIds(this.search.users).join(', ');
                    let separator = val.slice(-1) === ','
                        ? ','
                        : (val.slice(-2) === ', ' ? ', ' : '');
                    this.search.users = format + separator;
                }
            },
        },
    }
};
</script>
<style scoped>
    img.preview {
        height: 30px;
        float: left;
        padding-right: 15px;
        margin-top: 5px;
        border-radius: 5px;
    }

    small {
        display: block;
        color: gray;
        line-height: 1rem;
        overflow: hidden;
    }
</style>
