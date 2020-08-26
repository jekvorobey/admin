<template>
    <div class="pt-3">
        <div class="row">
            <div v-if="children.length > 0" @click="toggle" class="col-sm-6">
                <span :style="indent">
                    {{ category.name }}
                    <fa-icon :icon="opened ? 'angle-up' : 'angle-down'"></fa-icon>
                </span>
            </div>
            <div v-else class="col-sm-6">
                <span :style="indent">
                    {{ category.name }}
                </span>
            </div>
            <div class="col-sm-4">
                {{ category.code }}
            </div>
            <div class="col-sm-1">
                <span class="badge" :class="getBadgeClass(category.active)">
                    {{ category.active ? 'Да' : 'Нет' }}
                </span>
            </div>
            <div class="col-sm-1 mb-2 d-flex justify-items-center">
                <button class="btn btn-warning float-right" @click="editCategory">
                    <fa-icon icon="edit"></fa-icon>
                </button>
            </div>
        </div>
        <category-tree-item v-show="opened"
                            v-for="(child, index) in children"
                            :key="child.id"
                            :category="child"
                            :collection="collection"
                            :depth="depth + 1"
                            @onEdit="emit">
        </category-tree-item>
    </div>


</template>

<script>
    export default {
        name: "category-tree-item",
        props: {
            category: Object,
            collection: Array,
            depth: Number,
        },
        data() {
            return {
                opened: false,
            }
        },
        methods: {
            toggle() {
                this.opened = !this.opened;
            },
            emit(value) {
                this.$emit('onEdit', value);
            },
            editCategory() {
                this.emit(this.category);
            },
            getBadgeClass(active) {
                if (active) return 'badge-success';
                return 'badge-danger';
            }
        },
        computed: {
            indent() {
                return {
                    'margin-left': `${40 * this.depth}px`,
                };
            },
            children() {
                return this.collection.filter(category => {
                    return category.parent_id === this.category.id;
                });
            },
        },
    }
</script>

<style scoped>
    .row {
        margin-left: 0;
        margin-right: 0;
        border-bottom: 1px solid #dee2e6;
    }
</style>