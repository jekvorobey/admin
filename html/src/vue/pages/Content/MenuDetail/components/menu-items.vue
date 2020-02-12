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
                                </div>
                            </div>
                            <div class="row align-items-center my-2"
                            >
                                <new-menu-item @add="value => addSubItem(value, index)" placeholder="Новый подпункт"></new-menu-item>
                            </div>
                        </draggable>
                    </div>
                </div>
            </div>
            <div class="row align-items-center my-2"
            >
                <new-menu-item @add="addItem" placeholder="Новый пункт"></new-menu-item>
            </div>
        </draggable>

        {{selectedMenuItems}}
    </div>
</template>

<script>
    import Services from '../../../../../scripts/services/services';
    import draggable from 'vuedraggable';
    import {mapGetters} from 'vuex';
    import NewMenuItem from "./new-menu-item.vue";

    export default {
        components: {
            NewMenuItem,
            draggable
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
            };
        },
        methods: {
            addItem(value) {
                this.selectedMenuItems.push({name: value});
            },
            addSubItem(value, index) {
                this.selectedMenuItems[index].items.push({name: value});
            }
        },
        computed: {
            ...mapGetters(['getRoute']),
        },
        watch: {},
        mounted: function () {

        }
    }
</script>
