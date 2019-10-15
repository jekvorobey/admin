<template>
    <div @click="opened = !opened">
        <slot></slot>
        <fa-icon icon="angle-down" class="carret"></fa-icon>
        <transition name="slide">
            <div v-if="opened" class="dropdown-body shadow">
                <div class="wrapper">
                    <div v-for="item in items" @click="$emit('select', item.value)">{{item.text}}</div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        props: {
            items: Array,
        },
        data() {
            return {
                opened: false,
            };
        }
    }
</script>

<style scoped>
    .carret {
        position: relative;
        top: 3px;
    }
    .dropdown-body {
        position: absolute;
        background: white;
        z-index: 9999;
        border-radius: 5px;
    }

    .wrapper {
        padding: 13px 0;
    }

    .wrapper > div {
        padding: 7px 20px;
        transition: 0.3s all;
        cursor: pointer;
    }

    .wrapper > div:hover {
        color: #007BFF;
    }
</style>