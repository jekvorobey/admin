<template>
    <div :class="classes">
        <label>{{ title }}</label>
        <v-select2 v-model="form.categories"
                   class="form-control"
                   :multiple="true"
                   :selectOnClose="true"
                   @change="selectCategories"
                   width="100%">
            <option v-for="category in categoryOptions" :value="category.value">{{ category.text }}</option>
        </v-select2>

        <div class="mb-2 error">
            {{ error }}
        </div>
    </div>
</template>

<script>
    import VSelect2 from '../../../../components/controls/VSelect2/v-select2.vue';

    export default {
        components: {
            VSelect2
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
                buttonName: 'Выбрать категорию',
                form: {
                    categories: []
                },
            }
        },
        methods: {
            selectCategories() {
                this.$emit('update', this.form.categories);
            },
            categoryFullName(id) {
                if (!(id in this.categoriesObject)) {
                    return null;
                }

                let names = [];
                let currentCategory = this.categoriesObject[id];
                names.push(currentCategory.name);

                while (true) {
                    let category = this.categoriesObject[currentCategory.id];
                    if (category.parent_id && category.parent_id in this.categoriesObject) {
                        let parentCategory = this.categoriesObject[category.parent_id];
                        if (parentCategory.depth >= currentCategory.depth) {
                            break;
                        }

                        names.push(parentCategory.name);
                        currentCategory = parentCategory;
                        continue;
                    }

                    break;
                }

                return names.reverse().join(' » ');
            },
        },
        computed: {
            categoriesObject() {
                return Object.fromEntries(Object.values(this.categories).map(category => [category.id, category]))
            },
            categoryOptions() {
                return Object.values(this.categories).map(category => ({
                    value: category.id,
                    text: this.categoryFullName(category.id)
                }));
            },
        },
        mounted() {
            this.form.categories = this.iCategories ? [...this.iCategories] : [];
        },
        watch: {
            iCategories(val) {
                this.form.categories = this.iCategories ? [...this.iCategories] : [];
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
