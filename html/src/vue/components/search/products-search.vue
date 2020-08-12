<template>
    <v-select2 v-model="productId"
            class="form-control"
            :multiple="false"
            :selectOnClose="true"
            @input="input"
            @change="change"
            width="100%">
        <option v-for="product in selectedProducts" :value="product.id">
            {{ product.vendor_code }} {{ product.name }}
        </option>
    </v-select2>
</template>

<script>
    import VSelect2 from '../controls/VSelect2/v-select2.vue';
    import Services from '../../../scripts/services/services';

    export default {
        name: 'products-search',
        components: {VSelect2},
        props: {
            model: {},
        },
        data() {
            return {
                products: [],
            }
        },
        methods: {
            search(query) {
                Services.showLoader();
                Services.net().get(this.getRoute('search.products'), {
                    query: query,
                }).then((data) => {
                    this.products = data.products;
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            input(data) {
                console.log('input', data);
            },
            change(data) {
                console.log('change', data);
            },
        },
        computed: {
            productId: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
            selectedProducts() {
                return Object.values(this.products).map(product => ({
                    value: product.id,
                    text: [product.vendor_code, product.name].join(' '),
                }));
            },
        }
    };
</script>