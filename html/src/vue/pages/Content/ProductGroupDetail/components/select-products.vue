<template>
    <div>
        Продукты<br>
        <b-input-group class="mb-2">
            <b-form-textarea rows="5" v-model="inputVendorCode" placeholder="Введите артикулы" />
        </b-input-group>

        <b-button v-on:click="addProduct" variant="info">Добавить</b-button>

        <b-table-simple v-if="report.length > 0" class="mt-3" small>
            <b-tbody>
                <b-tr v-for="(item, i) in report" :key="i">
                    <b-td style="width: 20%" :variant="item.variant">Артикул {{ item.vendorCode }}</b-td>
                    <b-td :variant="item.variant">{{ item.status }}</b-td>
                </b-tr>
            </b-tbody>
        </b-table-simple>

        <div v-show="products.length > 0" class="mt-3">
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
                report: [],

                debounce: null,
            };
        },
        methods: {
            async addProduct() {
                const productVendorCodes = this.inputVendorCode.split("\n");

                if (productVendorCodes.length > 0) {
                    Services.showLoader();
                    this.report = [];
                }

                for (const productVendorCode of productVendorCodes) {
                    try {
                        const data = await Services.net().get(
                            this.getRoute('productGroup.getProducts'),
                            {vendor_code: productVendorCode}
                        );

                        if (data && data[0]) {
                            const productId = data[0].id;

                            if (!this.selectedProductIds.includes(productId)) {
                                this.$emit('add', productId);
                                this.selectedProductIds.push(productId);

                                this.report.push({
                                    vendorCode: productVendorCode,
                                    variant: 'success',
                                    status: 'Добавлен'
                                });
                            } else {
                                this.report.push({
                                    vendorCode: productVendorCode,
                                    variant: 'warning',
                                    status: 'Повторный'
                                });
                            }
                        } else {
                            this.report.push({
                                vendorCode: productVendorCode,
                                variant: 'danger',
                                status: 'Не найден'
                            });
                        }
                    } catch (error) {
                        console.error(error);
                    }
                }

                Services.hideLoader();

                this.inputVendorCode = '';
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
                clearTimeout(this.debounce);
                this.debounce = setTimeout(() => {
                    this.fetchProducts(val);
                }, 150);
            }
        },
        mounted: function () {
            this.fetchProducts(this.selectedProductIds);
        }
    }
</script>
