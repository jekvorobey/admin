<template>
    <div class="container-fluid">
        <draggable v-model="selectedMenuItems"
                   v-bind="dragOptions"
        >
            <div v-for="(item, index) in selectedMenuItems"
                 class="row align-items-center my-2 bg-light"
            >
                <div class="col border border-dark rounded p-2">
                    {{item.name}} (<a :href="item.url">{{item.url}}</a>)<br>
                    Опции: {{item.options}}

                    <button class="btn btn-success btn-sm" @click="editItem(item)">
                        <fa-icon icon="pencil"/>
                    </button>

                    <div class="container-fluid">
                        <draggable v-model="item.items"
                                   v-bind="dragOptions"
                        >
                            <div v-for="subitem in item.items"
                                 class="row align-items-center my-2 bg-light"
                            >
                                <div class="col border border-dark rounded p-2">
                                    {{subitem.name}} (<a :href="subitem.url">{{subitem.url}}</a>)<br>
                                    Опции: {{subitem.options}}

                                    <button class="btn btn-success btn-sm" @click="editItem(item)">
                                        <fa-icon icon="pencil"/>
                                    </button>
                                </div>
                            </div>
                            <div class="row align-items-center my-2">
                                <button class="btn btn-success btn-sm" @click="createItem(item)">
                                    <fa-icon icon="plus"/>
                                </button>
                            </div>
                        </draggable>
                    </div>
                </div>
            </div>
            <div class="row align-items-center my-2">
                <button class="btn btn-success btn-sm" @click="createItem()">
                    <fa-icon icon="plus"/>
                </button>
            </div>
        </draggable>

        <form-modal modal-name="FormTheme" @accept="applyItem" :model.sync="activeItem"/>

        {{selectedMenuItems}}
    </div>
</template>

<script>
    import Services from '../../../../../scripts/services/services';
    import draggable from 'vuedraggable';
    import {mapGetters} from 'vuex';
    import NewMenuItem from "./new-menu-item.vue";
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
            fillTheme(item) {
                return {
                    id: item ? item.id : null,
                    name: item ? item.name : '',
                    url: item ? item.url : '',
                    options: item ? item.options : {},
                    parent_id: item ? item.parent_id : null,
                    items: item ? item.items : [],
                }
            },
            createItem(parentItem) {
                this.activeItem = this.fillTheme();

                if (parentItem) {
                    this.activeItem.parent_id = parentItem.id;
                }

                this.openModal('FormTheme');
            },
            editItem(item) {
                this.activeItem = this.fillTheme(item);
                this.openModal('FormTheme');
            },
            applyItem() {
                if (this.activeItem.id) {
                    if (this.activeItem.parent_id) {
                        const index = this.activeItem.parent_id;
                        this.selectedMenuItems[index].items;
                    } else {
                        this.selectedMenuItems.push(this.activeItem);
                    }
                } else {
                    if (this.activeItem.parent_id) {
                        const parentId = this.activeItem.parent_id;
                        const parentIndex = _.findIndex(this.selectedMenuItems, {id: parentId});
                        this.selectedMenuItems[parentIndex].items.push(this.activeItem);
                    } else {
                        this.selectedMenuItems.push(this.activeItem);
                    }
                }

                this.closeModal('FormTheme');
                this.activeItem = null;
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
        },
        watch: {},
        mounted: function () {

        }
    }
</script>
