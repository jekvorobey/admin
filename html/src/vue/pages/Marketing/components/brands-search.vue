<template>
    <div :class="classes">
        <label v-if="title">{{ title }}</label>
        <v-select2 v-model="form.brands"
                   class="form-control"
                   :multiple="true"
                   :selectOnClose="true"
                   @change="selectCategories"
                   width="100%">
            <option v-for="brand in selectedBrands" :value="brand.value">{{ brand.text }}</option>
        </v-select2>

        <div class="mb-2 error">
            {{ error }}
        </div>
    </div>
</template>

<script>
    import VSelect2 from '../../../components/controls/VSelect2/v-select2.vue';

    export default {
        components: {
            VSelect2
        },
        props: {
            title: String,
            classes: String,
            brands: Array,
            iBrands: Array,
            error: String,
        },
        data() {
            return {
                buttonName: 'Выбрать бренд',
                form: {
                    brands: []
                },
            }
        },
        methods: {
            selectCategories() {
                this.$emit('update', this.form.brands);
            },
        },
        computed: {
            selectedBrands() {
                return Object.values(this.brands).map(brand => ({
                    value: brand.id,
                    text: brand.name
                }));
            },
        },
        mounted() {
            this.form.brands = this.iBrands ? [...this.iBrands] : [];
        },
        watch: {
            iBrands(val) {
                this.form.brands = this.iBrands ? [...this.iBrands] : [];
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
