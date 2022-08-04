<template>
    <svg :class="modifier">
        <use v-bind="{ 'xlink:href': `#icon-${name}` }"></use>
    </svg>
</template>

<script>
    export default {
        name: 'v-svg',
        props: {
            name: { type: String, default: null },
            modifier: { type: String, default: null },
        }
    };
</script>

<style scoped>
    .fill-white{
        fill: #fff;
    }
    .fill-black{
        fill: #000000;
    }
</style>