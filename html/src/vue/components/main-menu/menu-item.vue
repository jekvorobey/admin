<template>
    <div class="menu-item">
        <span v-if="item.items" @click="toggleItem()" class="menu-text heading" :class="{active: active}" :style="indent" >
            {{ item.title }}
            <fa-icon :icon="opened ? 'angle-down' : 'angle-right'" class="float-right shevron"></fa-icon>
        </span>
        <a v-else :href="item.route" class="menu-text" :class="{active: active, empty: item.route === '#'}" :style="indent">{{ item.title }}</a>
        <transition name="slide">
            <div v-if="item.items && opened" class="menu-items">
                <menu-item v-for="(subitem, index) in item.items" :item="subitem" :key="index" :depth="depth + 1"></menu-item>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        name: "menu-item",
        props: {
            item: {},
            depth: {
                type: Number,
                default: 1
            }
        },
        data() {
            return {
                toggle: null,
            };
        },
        methods: {
            checkItemsHasActive(item) {
                let result = false;
                if (item.active) {
                    result = true;
                } else {
                    if (item.items) {
                        for (let subitem of item.items) {
                            if (this.checkItemsHasActive(subitem)) {
                                result = true;
                                break;
                            }
                        }
                    }
                }
                return result;
            },
            toggleItem() {
                if (this.item.route) {
                    return;
                } else {
                    if (this.toggle === null) {
                        this.toggle = !this.active;
                    } else {
                        this.toggle = !this.toggle;
                    }
                }
            },
        },
        computed: {
            indent() {
                return {
                    'padding-left': `${16 * this.depth}px`,
                };
            },
            active() {
                return this.checkItemsHasActive(this.item);
            },
            opened() {
                if (this.toggle === null) {
                    return this.active;
                } else {
                    return this.toggle;
                }
            }
        }
    }
</script>

<style scoped>
    .active {
        background: #e8e8e8;
    }
    .empty {
        color: #6c0000 !important;
    }
    .menu-item {
        border-bottom: 1px solid #DFDFDF;
        min-height: 48px;
        cursor: pointer;
    }
    .menu-item:first-of-type {
        border-top: 1px solid #DFDFDF;
    }
    .menu-text {
        display: block;
        padding: 16px 0;
        color: #343A40;
        user-select: none;
    }
    .heading {
        font-weight: bold;
        color: #343A40;
    }
    .heading:hover {
        opacity: 80%;
    }
    .shevron {
        position: relative;
        top: 4px;
        right: 16px;
    }
</style>
