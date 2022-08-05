<template>
    <b-navbar toggleable="xl" class="p-0">
        <b-collapse id="nav-collapse" is-nav>
            <div class="sidebar-sticky">
                <menu-item v-for="(item, index) in menu" :item="item" :key="index"></menu-item>
            </div>
        </b-collapse>
    </b-navbar>
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
        min-width: 100%;
        position: -webkit-sticky;
        position: sticky;
        top: 48px;
        overflow-x: hidden;
        overflow-y: auto;
    }
</style>