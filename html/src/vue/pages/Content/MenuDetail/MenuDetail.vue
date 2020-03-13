<template>
    <layout-main back>
        <b-form @submit.prevent="update">
            <h2>{{menu.name}}</h2>

            <menu-items
                    :i-menu-items="selectedMenuItems"
                    @update="onUpdateMenuItems"
            >
            </menu-items>

            <b-button class="mt-5" type="submit" variant="dark">Сохранить</b-button>
        </b-form>
    </layout-main>
</template>

<script>
    import Services from '../../../../scripts/services/services';
    import MenuItems from './components/menu-items.vue';
    import modalMixin from '../../../mixins/modal';

    export default {
        components: {
            MenuItems
        },
        mixins: [modalMixin],
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
                        this.showMessageBox({title: 'Изменения сохранены'});
                        window.location.href = this.route('menu.list');
                    })
                    .catch(() => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    });
            },
            onUpdateMenuItems(data) {
                this.selectedMenuItems = data;
            },
        },
    };
</script>

<style scoped>
</style>
