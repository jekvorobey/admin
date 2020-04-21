<template>
    <div>
        <span v-for="(category_item, index) in sortedTree"><span v-if="index !== 0"> >> </span>{{ category_item.name
            }}</span>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                tree: []
            };
        },
        props: {
            product: Object,
            propertyValues: {},
            options: Object
        },
        methods: {
            values(id) {
                return this.propertyValues[id] || [];
            },
            categoryGet(catId) {
                let category = Object.values(this.options.categories).find(category => category.id === catId);
                return category ? category : false;
            },
            categoryTree(catId) {
                let category = this.categoryGet(catId);
                if(category) {
                    this.tree.push(category)
                    if(category.parent_id) {
                        this.categoryTree(category.parent_id)
                    }
                }
            },
        },
        mounted() {
            this.categoryTree(this.product.category_id);
        },
        computed: {
            sortedTree: function () {
                return this.tree.sort((a, b) => a > b ? 1 : -1);
            }
        }
    }
</script>

<style scoped>

</style>