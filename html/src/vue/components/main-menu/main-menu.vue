<template>
    <nav class="d-none d-md-block bg-light sidebar">
        <div class="sidebar-sticky">
            <div v-for="group in menu" :key="group.title" class="p-2">
                <h5><img v-if="group.icon" :src="group.icon" class="menu-icon" :alt="group.title">{{ group.title }}</h5>
                <a
                        v-for="link in group.items"
                        :key="JSON.stringify(link.route)"
                        :href="link.route" class="nav-link"
                        :class="{'bg-secondary text-light':link.active, 'pl-5':link.subitem}"
                >{{ link.title }}</a>
            </div>
        </div>
    </nav>
</template>

<script>
    import './main-menu.css';

    export default {
        name: 'main-menu',
        computed: {
            menu() {
                return this.$store.state.layout.menu;
            },
        },
    };
</script>