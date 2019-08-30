<template>
    <nav class="d-none d-md-block bg-light sidebar">
        <div class="sidebar-sticky">
            <menu-item v-for="(item, index) in menu" :item="item" :key="index"></menu-item>
        </div>
    </nav>
</template>

<script>
    import MenuItem from './menu-item.vue';

    export default {
        name: 'main-menu',
        components: {
            MenuItem
        },
        computed: {
            menu() {
                return this.$store.state.layout.menu;
            },
        },
    };
</script>
<style>
    .sidebar-sticky {
        position: -webkit-sticky;
        position: sticky;
        top: 48px;
        overflow-x: hidden;
        overflow-y: auto;
    }
</style>