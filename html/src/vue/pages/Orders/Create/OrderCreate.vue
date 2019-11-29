<template>
    <layout-main>
        <div class="select">
            <div class="row">
                <f-input class="col-4" :model="product.article" @change="productChange()">
                    Артикулы товаров (через ,)
                </f-input>
                <ul v-if="searchedProducts.length > 0" v-for="(product, key) in searchedProducts">
                    <li>
                        {{ product.name }} - {{ product.articul }}
                    </li>
                </ul>
                <div class="col-8">
                    Товары:
                    selectedProducts
                </div>
            </div>
            <div class="row">
                <f-input class="col-4" :model="user" @change="customerChange()">
                    ID, ФИО или e-mail пользователя
                </f-input>
                <div class="col-8">
                    Пользователь:
                    selectedUser
                </div>
            </div>
            <div class="row">
                <f-select class="col-4" :model="user" :options="avaliableDeliveries">
                    Служба доставки
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

import Service from "../../../../scripts/services/services";
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
            product: {},
            searchedProducts: {},
        }
    },
    methods: {
        checkOrder() {
            return false;
        },
        productChange() {
            console.log('productChange');
        },
        customerChange() {
            console.log('customerChange');
        }
    },
    computed: {
        ...mapGetters(['getRoute']),

        avaliableDeliveries() {
            return [
                {
                    value: 1,
                    text: 'DPD'
                },
                {
                    value: 2,
                    text: 'Another one'
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
