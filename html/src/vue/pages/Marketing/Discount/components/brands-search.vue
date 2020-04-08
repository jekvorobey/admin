<template>
    <div :class="classes">
        <label for="brand-select">{{ title }}</label>
        <div class="input-group mb-1">
            <div class="d-flex flex-row form-control" :class="{ 'input-error': error }">
                <div v-for="brand in selectedBrands"
                     @click="toggleBrand(brand.id)"
                     class="badge badge-secondary mr-2 cursor-pointer"
                     id="brand-select"
                >{{ brand.name }} <fa-icon icon="trash-alt"></fa-icon></div>
            </div>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" v-b-toggle="'brand-collapse-' + _uid">Выбрать бренд</button>
            </div>
        </div>
        <div class="mb-2 error">
            {{ error }}
        </div>
        <b-collapse :id="'brand-collapse-' + _uid" class="mt-2">
            <b-card>
                <div v-for="brand in brands"
                     @click="toggleBrand(brand.id)"
                     class="badge badge-secondary mr-2 cursor-pointer"
                >{{ brand.name }}</div>
            </b-card>
        </b-collapse>
    </div>
</template>

<script>
    export default {
        props: {
            title: String,
            classes: String,
            brands: Array,
            iBrands: Array,
            error: String,
        },
        data() {
            return {
                brand: [],
            }
        },
        methods: {
            toggleBrand(brandId) {
                let brands = new Set(this.brand);
                if (brands.has(brandId)) {
                    brands.delete(brandId);
                } else {
                    brands.add(brandId);
                }
                this.brand = [...brands];

                this.$emit('update', this.brand);
            },
        },
        computed: {
            selectedBrands() {
                let selected = new Set(this.brand);
                return this.brands.filter(brand => selected.has(brand.id));
            },
        },
        mounted() {
            this.brand = this.iBrands ? [...this.iBrands] : [];
        },
        watch: {
            iBrands(val) {
                this.brand = this.iBrands ? [...this.iBrands] : [];
            }
        },
    }
</script>

<style>
    .error {
        color: red;
        font-size: small;
    }
    .input-error {
        border-color: red;
    }
</style>
