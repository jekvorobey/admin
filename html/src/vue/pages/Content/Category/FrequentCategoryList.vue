<template>
    <layout-main>
        <div class="mt-5 mb-4">
            <span class="ml-1">Выбрано категорий: {{ countSelected }}</span>
            <button v-if="canUpdate(blocks.content)" class="btn-success btn float-right" @click="save()" :disabled="saveBtnDisabled">Сохранить</button>
        </div>
        <table class="table mb-0">
            <thead>
            <tr class="d-flex">
                <th class="col-sm-4 col-lg-5">Название категории</th>
                <th class="col-sm-2 col-lg-1">
                    Быстрая категория
                    <fa-icon icon="question-circle" v-b-popover.hover="frequentTooltip"></fa-icon>
                </th>
                <th class="col-sm-2">
                    Порядок отображения
                    <fa-icon icon="question-circle" v-b-popover.hover="positionTooltip"></fa-icon>
                </th>
                <th class="col-sm-4">Иконка категории</th>
            </tr>
            </thead>
        </table>
        <tree-item
                v-for="item in basicCategories"
                :key="item.id"
                :category="item"
                :collection="categories"
                :depth="0"
                :selectable="selectable"
                @onEdit="editTreeItem"
        ></tree-item>
    </layout-main>
</template>

<script>

    import Services from '../../../../scripts/services/services';
    import TreeItem from './components/frequent-category-tree-item.vue';

    export default {
        components: {
            TreeItem
        },
        props: {
            categories: Array,
            frequentMaxCount: Number,
        },
        data() {
            return {
                checkboxes: this.categories.filter((value) => {
                    return value.frequent;
                }).map((value) => {
                    return value.id;
                }),
                editedItems: {},
            }
        },
        methods: {
            editTreeItem(value) {
                let {item, invalid} = value;
                let index = this.checkboxes.indexOf(item.id);
                if (item.frequent && index === -1) {
                    this.checkboxes.push(item.id);
                } else if (!item.frequent && index !== -1) {
                    this.checkboxes.splice(index, 1);
                }

                this.$set(this.editedItems, item.id, value);
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
            frequentTooltip() {
                return 'Выбранные категории будут отображаться на главной странице (не более ' + this.frequentMaxCount + ' категорий)';
            },
            positionTooltip() {
                return 'В порядке возрастания значения'
            },
            countSelected() {
                return this.checkboxes.length;
            },
            selectable() {
                return this.countSelected < this.frequentMaxCount;
            },
            anyInvalid() {
                return Object.values(this.editedItems).some((value) => {
                    return value.invalid;
                });
            },
            saveBtnDisabled() {
                return (Object.keys(this.editedItems).length < 1 || this.anyInvalid);
            },
        }
    };
</script>