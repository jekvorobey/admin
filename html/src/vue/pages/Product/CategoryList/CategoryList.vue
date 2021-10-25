<template>
    <layout-main>
        <div class="mt-5 mb-4" v-if="canUpdate(blocks.products)">
            <button class="btn-success btn" @click="createCategory">Создать категорию</button>
        </div>
        <table class="table mb-0">
            <thead>
            <tr class="d-flex">
                <th class="col-sm-6">Название категории</th>
                <th class="col-sm-4">
                    Код
                </th>
                <th class="col-sm-1">
                    Активна
                </th>
                <th v-if="canUpdate(blocks.products)" class="col-sm-1">Действия</th>
            </tr>
            </thead>
        </table>
        <tree-item
                v-for="item in basicCategories"
                :key="item.id"
                :category="item"
                :collection="categories"
                :depth="0"
                @onEdit="editCategory"
        ></tree-item>

        <category-edit-modal
                :category="categoryToEdit"
                :collection="categories"
        />

    </layout-main>
</template>

<script>

    import TreeItem from './components/category-tree-item.vue';
    import CategoryEditModal from './components/category-edit-modal.vue';

    export default {
        components: {
            TreeItem,
            CategoryEditModal,
        },
        props: {
            categories: Array,
        },
        data() {
            return {
                categoryToEdit: null,
            }
        },
        methods: {
            createCategory() {
                this.categoryToEdit = null;
                this.$bvModal.show('category-edit-modal');
            },
            editCategory(value) {
                this.categoryToEdit = value;
                this.$bvModal.show('category-edit-modal');
            },
        },
        computed: {
            basicCategories() {
                return this.categories.filter(function (category) {
                    return (!category.parent_id);
                });
            },
        }
    };
</script>