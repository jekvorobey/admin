<template>
    <layout-main back>
        <b-form @submit.prevent="update">
            <p>Наименование: {{menu.name}}</p>
            <p>Символьный код: {{menu.code}}</p>

            <menu-items
                    :i-menu-items="selectedMenuItems"
                    @update="onUpdateMenuItems"
            >
            </menu-items>

            <b-button type="submit" variant="dark">Обновить</b-button>
        </b-form>
    </layout-main>
</template>

<script>
    import {mapGetters} from "vuex";
    import Services from "../../../../scripts/services/services";
    import MenuItems from './components/menu-items.vue';

    export default {
        components: {
            MenuItems
        },
        props: {
            iMenu: {},
            options: {}
        },
        data() {
            return {
                menu: this.iMenu,
                selectedMenuItems: this.iMenu['items_tree'],
            };
        },

        methods: {
            update() {
                let model = this.menu;
                model.items = this.selectedMenuItems;

                Services.net()
                    .put(this.getRoute('menu.update', {id: this.menu.id,}), {}, model)
                    .then((data) => {
                        console.log(data);
                    });
            },
            onUpdateMenuItems(data) {
                this.selectedMenuItems = data;
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
        },
    };
</script>

<style scoped>
</style>
