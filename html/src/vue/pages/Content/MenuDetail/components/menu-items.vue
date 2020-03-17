<template>
    <div class="container-fluid">
        <draggable v-model="selectedMenuItems"
                   v-bind="dragOptions"
        >
            <!-- Первый уровень -->
            <div v-for="(item, itemIndex) in selectedMenuItems"
                 class="row my-2 bg-light border border-dark rounded"
            >
                <div class="col p-2">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col align-self-center">
                                <b>{{item.name}}</b> ({{item.url}})<br>
                            </div>
                            <div class="col-auto">
                                <b-button class="btn btn-success btn-sm" @click="editItem(itemIndex)">
                                    <fa-icon icon="edit"/>
                                </b-button>
                                <b-button class="btn btn-danger btn-sm">
                                    <fa-icon icon="trash-alt" @click="removeItem(itemIndex)"/>
                                </b-button>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <draggable v-model="item.children"
                                   v-bind="dragOptions"
                        >
                            <!-- Второй уровень -->
                            <div v-for="(child, childIndex) in item.children"
                                 class="row my-2 bg-light border border-dark rounded"
                            >
                                <div class="col p-2">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col align-self-center">
                                                {{child.name}} ({{child.url}})
                                            </div>
                                            <div class="col-auto">
                                                <b-button class="btn btn-success btn-sm"
                                                          @click="editItem(childIndex, itemIndex)">
                                                    <fa-icon icon="edit"/>
                                                </b-button>
                                                <b-button class="btn btn-danger btn-sm">
                                                    <fa-icon icon="trash-alt"
                                                             @click="removeItem(childIndex, itemIndex)"/>
                                                </b-button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-2">
                                <b-button class="btn btn-success btn-sm" @click="createItem(itemIndex)">
                                    <fa-icon icon="plus"/>
                                    Добавить подпункт
                                </b-button>
                            </div>
                        </draggable>
                    </div>
                </div>
            </div>
            <div class="row my-2">
                <b-button class="btn btn-success btn-sm" @click="createItem()">
                    <fa-icon icon="plus"/>
                    Добавить пункт
                </b-button>
            </div>
        </draggable>

        <form-modal modal-name="FormTheme" @accept="onModalAccept" :model.sync="activeItem"/>
    </div>
</template>

<script>
    import draggable from 'vuedraggable';
    import NewMenuItem from './new-menu-item.vue';
    import modalMixin from '../../../../mixins/modal';
    import FormModal from './form-modal.vue';

    export default {
        mixins: [modalMixin],
        components: {
            NewMenuItem,
            draggable,
            FormModal
        },
        props: {
            iMenuItems: Array,
            dragOptions: {
                animation: 200,
                sort: true,
            },
        },
        data() {
            return {
                selectedMenuItems: this.iMenuItems,
                activeItem: null,
            };
        },
        methods: {
            /**
             * Подготовить активный (тот что редактируется или создаётся) пункт меню, чтобы его передать в попап
             * @param {Object} item - существующий пункт меню, если есть
             * @param {Number} itemIndex - индекс редактируемого пункта, если есть
             * @param {Number|Undefined} parentIndex - индекс родительского пункта, если редактируем потомка
             */
            fillItem(item, itemIndex, parentIndex) {
                return {
                    id: item ? item.id : null,
                    name: item ? item.name : '',
                    url: item ? item.url : '',
                    options: item ? item.options : {},
                    parent_id: item ? item.parent_id : null,
                    children: item ? item.children : [],
                    _new: !item,
                    _index: typeof itemIndex !== 'undefined' ? itemIndex : null,
                    _parentIndex: typeof parentIndex !== 'undefined' ? parentIndex : null,
                }
            },
            /**
             * Создать новый пункт меню
             * @param {Number|Undefined} parentIndex - индекс родительского элемента, если создаём потомка
             */
            createItem(parentIndex) {
                this.activeItem = this.fillItem(false, false, parentIndex);

                if (typeof parentIndex !== 'undefined') {
                    this.activeItem.parent_id = this.fetchItem(parentIndex).id;
                }

                this.openModal('FormTheme');
            },
            /**
             * Отредактировать существующий пункт меню
             * @param {Number} itemIndex - индекс редактируемого пункта
             * @param {Number|Undefined} parentIndex - индекс родительского пункта, если редактируем потомка
             */
            editItem(itemIndex, parentIndex) {
                this.activeItem = this.fillItem(this.fetchItem(itemIndex, parentIndex), itemIndex, parentIndex);
                this.openModal('FormTheme');
            },
            /**
             * Удалить существующий пункт меню
             * @param {Number} itemIndex - индекс удаляемого пункта
             * @param {Number|Undefined} parentIndex - индекс родительского пункта, если удаляем потомка
             */
            removeItem(itemIndex, parentIndex) {
                if (typeof parentIndex !== 'undefined') {
                    this.selectedMenuItems[parentIndex].children.splice(itemIndex, 1);
                } else {
                    this.selectedMenuItems.splice(itemIndex, 1);
                }
            },
            /**
             * Получить пункт меню
             * @param {Number} itemIndex - индекс пункта
             * @param {Number|Undefined} parentIndex - индекс родительского пункта, если получаем потомка
             */
            fetchItem(itemIndex, parentIndex) {
                if (typeof parentIndex !== 'undefined') {
                    return this.selectedMenuItems[parentIndex].children[itemIndex];
                }

                return this.selectedMenuItems[itemIndex];
            },
            /**
             * Обработка результатов попапа.
             * Было либо редактирование, либо создание пункта меню
             */
            onModalAccept() {
                if (this.activeItem._new) {
                    // Новый элемент
                    if (this.activeItem._parentIndex === null) {
                        // Первый уровень
                        this.selectedMenuItems.push(this.activeItem);
                    } else {
                        // Второй уровень
                        this.selectedMenuItems[this.activeItem._parentIndex].children.push(this.activeItem);
                    }
                } else {
                    // Существующий элемент
                    if (this.activeItem._parentIndex === null) {
                        // Первый уровень
                        this.selectedMenuItems[this.activeItem._index] = this.activeItem;
                    } else {
                        // Второй уровень
                        this.selectedMenuItems[this.activeItem._parentIndex].children[this.activeItem._index] = this.activeItem;
                    }
                }

                this.closeModal('FormTheme');
                this.activeItem = null;
            },
        },
    }
</script>
