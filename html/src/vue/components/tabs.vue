<template>
    <ul class="nav nav-tabs mt-5">
        <li v-for="item in items" class="nav-item">
            <a class="nav-link" :class="{active: item.value === current }" @click="$emit('nav', item.value)">{{ item.text }}</a>
        </li>
    </ul>
</template>

<script>
    export default {
        props: {
            current: String,
            items: {}
        }
    }
</script>