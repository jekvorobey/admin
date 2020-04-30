<template>
    <layout-main>
        <div class="select">
            <div class="row">
                <div class="col-8">
                    <f-input v-model="search.products" @change="productChange()" placeholder="e908-3543-9fdd,85de-3283-8b96">
                        Артикулы товаров (через ,)
                    </f-input>
                    <div v-if="searchedProducts">
                        <ul class="list-group mt-2">
                            <li class="list-group-item" :class="selectedOffers[product.id] ? 'bg-light' : ''" v-for="(product, key) in searchedProducts">
                                <div class="d-flex w-100 justify-content-between">
                                    <div>
                                        <img :src="product.photo" class="preview" :alt="product.name"
                                             v-if="product.photo">
                                        {{ product.name }}
                                        <small>{{ product.vendor_code }}</small>
                                    </div>
                                    <div v-if="selectedOffers[product.id]">
                                         {{ product.qty }} шт.
                                        <button class="btn btn-sm btn-dark" @click="changeProduct(product, false)">-</button>
                                        <button class="btn btn-sm btn-dark" @click="changeProduct(product, true)">+</button>
                                        <fa-icon icon="times" class="ml-3" @click="deleteProduct(key)"></fa-icon>
                                    </div>
                                </div>
                                <div v-for="offer in product.offers" :key="offer.id" class="offers">
                                    <hr>
                                    <div class="d-flex w-100 justify-content-between">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" :id="`check-${offer.id}`" v-model="selectedOffers[product.id]" :value="offer.id" class="custom-control-input">
                                            <label class="custom-control-label" :for="`check-${offer.id}`">
                                                <span class="badge badge-secondary badge-pill">{{ saleStatusName(offer.sale_status) }}</span>
                                                {{ offer.xml_id }}
                                            </label>
                                        </div>
                                        ({{ offerMerchant(offer) }})
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <f-select class="col-1" v-model="selectedUserType" :options="userTypeSelect" without_none>
                    Пользователь
                </f-select>
                <f-input v-model="search.customer" @change="customerChange()" :disabled="Object.keys(selectedOffers) < 1">
                    &nbsp;
                </f-input>
                <div v-if="searchedCustomer">
                    <ul class="list-group mt-2">
                        <li class="list-group-item" :class="selectedOffers[product.id] ? 'bg-light' : ''" v-for="(product, key) in searchedProducts">
                        </li>
                    </ul>
                </div>

                <div class="col-8">
                    {{ searchedCustomer }}

                </div>
            </div>
        </div>

        <button class="btn btn-success" :disabled="checkOrder" @click="createOrder">Создать заказ</button>
    </layout-main>
</template>

<script>

    import Services from '../../../../scripts/services/services';

    import FInput from '../../../components/filter/f-input.vue';
    import FDate from '../../../components/filter/f-date.vue';
    import FSelect from '../../../components/filter/f-select.vue';
    import FMultiSelect from '../../../components/filter/f-multi-select.vue';
    import Dropdown from '../../../components/dropdown/dropdown.vue';

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
            searchedCustomer: {},
            selectedProducts: {},
            searchedStocks: {},
            searchedMerchants: {},
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
                        this.searchedMerchants = result.merchants;

                        console.log(result); // TODO: DEL
                    }
                });
        },
        customerChange() {
            Services.net().post(this.getRoute('orders.searchCustomer', null), {}, {
                type: this.selectedUserType,
                search: this.search.customer
            })
                .then(result => {
                    this.search.customer = {};
                    if(result) {
                        this.search.customer = result;
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
        deleteProduct(key) {
            const product = this.searchedProducts[key];
            // this.$delete(this.searchedProducts, key);
            this.$delete(this.selectedOffers, product.id);
        },
        changeProduct(product, action) {
            if(!action && product.qty === 1) {
                return;
            }

            action ? product.qty++ : product.qty--;
            this.$set(this.selectedProducts, product.id, product);
        },
        selectOffer(product) {
            // this.$set(this.selectedOffers[product.id], 'qty', 1);
        },
        offerMerchant(offer) {
            return 'merchant';
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

    .offers {
        max-height: 200px;
        overflow: auto;
    }
</style>
