<template>
    <layout-main>
        <div class="mt-5 mb-4">
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
                <th class="col-sm-1">Действия</th>
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

    import Services from '../../../../scripts/services/services';
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
                console.log('emitted');
                console.log(this.categoryToEdit);
                this.$bvModal.show('category-edit-modal');
            },
            openEditModal() {
                this.$bvModal.show('category-edit-modal');
            },
            save() {
                if (this.anyInvalid) {
                    return;
                }
                let data = Object.values(this.editedItems).map((value) => {
                    return {
                        'id': value.item.id,
                        'frequent': value.item.frequent,
                        'position': parseInt(value.item.position),
                        'file_id': value.item.image ? value.item.image.id : null,
                    };
                });

                Services.showLoader();
                Services.net().put(this.getRoute('frequentCategories.edit'), {}, {
                    'items': data,
                    'selected': this.checkboxes,
                }, {}, true).then((data) => {
                    Services.msg("Данные сохранены!");
                    this.editedItems = {};
                }, () => {
                    Services.msg("Не удалось сохранить данные", 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
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