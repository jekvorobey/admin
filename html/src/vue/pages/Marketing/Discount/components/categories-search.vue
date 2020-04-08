<template>
    <div :class="classes">
        <label for="category-select">{{ title }}</label>
        <div class="input-group mb-1" :class="{ 'input-error': error }">
            <div class="d-flex flex-row form-control">
                <div v-for="category in selectedCategories"
                     @click="toggleCategory(category.id)"
                     class="badge badge-secondary cursor-pointer mr-2"
                     id="category-select"
                >{{ category.name }}
                    <fa-icon icon="trash-alt"></fa-icon>
                </div>
            </div>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" v-b-toggle="'category-collapse-' + _uid">Выбрать категорию</button>
            </div>
        </div>
        <div class="mb-2 error">
            {{ error }}
        </div>
        <b-collapse :id="'category-collapse-' + _uid" class="mt-2">
            <b-card>
                <category-tree
                        v-for="category in categories"
                        :key="category.id"
                        :category="category"
                        :collection="categories"
                        :depth="0"
                >
                </category-tree>
            </b-card>
        </b-collapse>
    </div>
</template>

<script>
    import CategoryTree from '../../../../components/category-tree/category-tree.vue';
    import Services from "../../../../../scripts/services/services";

    export default {
        components: {
            CategoryTree
        },
        props: {
            title: String,
            classes: String,
            categories: Array,
            iBrands: Array,
            iCategories: Array,
            error: String,
        },
        data() {
            return {
                category: [],
            }
        },
        methods: {
            toggleCategory(categoryId) {
                let categories = new Set(this.category);
                if (categories.has(categoryId)) {
                    categories.delete(categoryId);
                } else {
                    categories.add(categoryId);
                }
                this.category = [...categories];
                this.$emit('update', this.category);
            },
        },
        computed: {
            selectedCategories() {
                let selected = new Set(this.category);
                return this.categories.filter(category => selected.has(category.id));
            },
        },
        mounted() {
            this.category = this.iCategories ? [...this.iCategories] : [];
        },
        created() {
            Services.event().$on('select-category', categoryId => {
                this.toggleCategory(categoryId);
            });
        },
        watch: {
            iCategories(val) {
                this.category = this.iCategories ? [...this.iCategories] : [];
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