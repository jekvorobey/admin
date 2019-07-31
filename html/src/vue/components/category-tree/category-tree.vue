<template>
    <div class="category-tree">
        <div @click="emitBus('select-category', category.id)" :style="indent" class="category-tree__row" :class="{'parent-row': depth === 0}">
            <span :class="{title: depth === 0}">{{ category.name }}</span>
            <span class="category-tree__row-buttons"></span>
        </div>
        <div>
            <category-tree v-for="(child, index) in children"
                            :key="child.id"
                            :category="child"
                            :collection="collection"
                            :depth="depth + 1"
                            :last="index === (children.length - 1)">
            </category-tree>
        </div>
    </div>
</template>

<script>
    import Services from "../../../scripts/services/services";

    export default {
        name: "category-tree",
        props: {
            category: Object,
            collection: Array,
            depth: Number,
            last: {
                type: Boolean,
                default: false
            }
        },
        methods: {
            emitBus(event, data) {
                Services.event().$emit(event, data);
            }
        },
        computed: {
            indent() {
                let style = {
                    'margin-left': `${30 * this.depth}px`,
                };
                if (!this.last) {
                    style['border-bottom'] = '1px solid gray';
                }
                return style;
            },
            children() {
                return this.collection.filter(category => {
                    return category.parent_id === this.category.id
                })
            }
        }
    }
</script>

<style scoped>
    .category-tree__row {
        margin-top: 2px;
        padding: 4px 8px;
        display: flex;
        justify-content: space-between;
    }
    .parent-row {
        border-top: 1px solid gray;
    }
    .title {
        font-weight: bold;
    }

</style>