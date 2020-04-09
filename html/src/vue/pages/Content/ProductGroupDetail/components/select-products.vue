<template>
    <div>
        Продукты<br>
        <b-input-group>
            <b-form-input v-model="inputVendorCode" placeholder="Введите артикул"/>
            <b-input-group-append>
                <b-button v-on:click="addProduct" variant="info">Добавить</b-button>
            </b-input-group-append>
        </b-input-group>

        <div v-show="products.length">
            Добавленные продукты<br>
            <b-card v-for="product in products"
                    no-body
                    class="overflow-hidden"
                    :key="product.id"
            >
                <b-row no-gutters>
                    <b-col md="1">
                        <b-card-img :src="product.photo"
                                    class="rounded-0"
                        />
                    </b-col>
                    <b-col md="10">
                        <b-card-body :title="product.name">
                            <b-card-text>
                                Артикул: {{ product.vendor_code }}
                            </b-card-text>
                        </b-card-body>
                    </b-col>
                    <b-col md="1">
                        <b-card-body>
                            <b-button variant="danger" @click="() => {removeProduct(product.id)}"><fa-icon icon="trash-alt"/></b-button>
                        </b-card-body>
                    </b-col>
                </b-row>
            </b-card>
        </div>
    </div>
</template>

<script>
    import Services from '../../../../../scripts/services/services';

    export default {
        components: {},
        props: {
            iSelectedProductIds: Array,
        },
        data() {
            return {
                selectedProductIds: this.iSelectedProductIds,
                inputVendorCode: '',
                products: [],
            };
        },
        methods: {
            addProduct() {
                const val = this.inputVendorCode;

                Services.net().get(this.getRoute('productGroup.getProducts'), {vendor_code: val})
                    .then((data) => {
                        if (data && data[0]) {
                            let productId = data[0].id;
                            this.$emit('add', productId);
                            this.selectedProductIds.push(productId);
                            this.inputVendorCode = '';
                        }
                    });
            },
            removeProduct(id) {
                this.selectedProductIds = this.selectedProductIds.filter((selectProductId) => {
                    return selectProductId !== id
                });

                this.$emit('delete', id);
            },
            fetchProducts(ids) {
                if (ids && (ids.length > 0)) {
                    Services.net().get(this.getRoute('productGroup.getProducts'), {id: ids})
                        .then((data) => {
                            this.products = data;
                        });
                } else {
                    this.products = [];
                }
            },
        },
        watch: {
            selectedProductIds(val) {
                this.fetchProducts(val);
            }
        },
        mounted: function () {
            this.fetchProducts(this.selectedProductIds);
        }
    }
</script>
