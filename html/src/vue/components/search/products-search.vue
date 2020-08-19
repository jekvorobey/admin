<template>
    <div class="autocomplete">
        <v-input
                type="text"
                v-model="query"
                @focus="onFocus"
                @focusin="onFocus"
                @focusout="onFocusOut"
                @input="onChange"
        ></v-input>
        <ul
                v-show="isOpen && query"
                class="autocomplete-results"
        >
            <li
                    v-for="(product, i) in products"
                    v-if="exceptedIds.indexOf(product.id) === -1"
                    :key="i"
                    @click="setResult(product)"
                    class="autocomplete-result with-small"
            >
                <small>{{ product.vendorCode }}</small> {{ product.name }}
            </li>
            <li v-if="!products.length" class="autocomplete-result">Ничего не найдено</li>
        </ul>
        <p v-for="product in foundedProducts">
            <a :href="getRoute('products.detail', {id: product.id})" target="_blank">
                {{ product.vendorCode }} {{ product.name }}
            </a>
            <button type="button" @click="deleteProduct(product.id)" class="btn"><fa-icon icon="trash-alt"/></button>
        </p>
    </div>
</template>

<script>
    import Services from '../../../scripts/services/services';
    import VInput from '../controls/VInput/VInput.vue';

    export default {
        name: 'products-search',
        components: {VInput},
        props: {
            model: {},
            exceptedIds: {
                type: Array,
                default: [],
            }
        },
        data() {
            return {
                query: '',
                products: [],
                isOpen: false,
            }
        },
        methods: {
            setResult(product) {
                this.foundedProducts[product.id] = product;
                this.isOpen = false;
            },
            onFocus() {
                if (this.products.length) {
                    this.isOpen = true;
                }
            },
            onFocusOut() {
                setTimeout(() => {
                    this.isOpen = false;
                }, 100)
            },
            onChange() {
                // Let's warn the parent that a change was made
                this.$emit('input', this.query);
                if (this.query.length < 3) {
                    return;
                }

                this.search();
            },
            search() {
                Services.net().get(this.getRoute('search.products'), {
                    query: this.query,
                    exceptedIds: this.exceptedIds,
                }).then((data) => {
                    this.isOpen = true;
                    this.products = data.products;
                });
            },
            deleteProduct(id) {
                this.$delete(this.foundedProducts, id);
            }
        },
        computed: {
            foundedProducts: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
        },
        watch: {
            query(value) {
                if (!value) {
                    this.products = [];
                }
            }
        }
    };
</script>

<style>
    .autocomplete {
        position: relative;
    }

    .autocomplete-results {
        padding: 0;
        margin-top: -1rem;
        border: 1px solid #eeeeee;
        min-height: 1px;
        max-height: 240px;
        overflow: auto;
        position: absolute;
        z-index: 9999;
        left: 0;
        right: 0;
        background-color: #fff;
    }

    .autocomplete-result {
        border-bottom: 1px solid #d4d4d4;
        list-style: none;
        text-align: left;
        padding: 4px 2px;
        cursor: pointer;
    }

    .autocomplete-result:hover {
        background-color: #337ab7;
        color: white;
    }
</style>
