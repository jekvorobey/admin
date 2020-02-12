<template>
    <layout-main back>
        <b-form @submit.prevent="update">
            <b-form-group
                    label="Наименование"
                    label-for="menu-name"
            >
                <b-form-input
                        id="menu-name"
                        v-model="menu.name"
                        type="text"
                        required
                        placeholder="Введите наименование"
                />
            </b-form-group>

            <b-form-group
                    label="Символьный код"
                    label-for="menu-code"
            >
                <b-form-input
                        id="menu-code"
                        v-model="menu.code"
                        type="text"
                        required
                        placeholder="Введите символьный код"
                />
            </b-form-group>

            <menu-items
                    :i-menu-items="selectedMenuItems"
                    @update="onUpdateMenuItems"
            >
            </menu-items>
            {{selectedMenuItems}}

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
            MenuItems,
        },
        props: {
            iMenu: {},
            options: {}
        },
        data() {
            return {
                menu: this.iMenu,
                selectedMenuItems: [{
                    id: 1,
                    name: '01',
                    url: '/',
                    options: {},
                    items: [],
                }, {
                    id: 2,
                    name: '02',
                    url: '/',
                    options: {},
                    items: [],
                }, {
                    id: 3,
                    name: '03',
                    url: '/',
                    options: {},
                    items: [{
                        id: 5,
                        name: '05',
                        url: '/',
                        options: {},
                        items: [],
                    }, {
                        id: 6,
                        name: '06',
                        url: '/',
                        options: {},
                        items: [],
                    }],
                }, {
                    id: 4,
                    name: '04',
                    url: '/',
                    options: {},
                    items: [],
                }],
            };
        },

        methods: {
            refresh() {
                Services.net().get(this.getRoute('menu.detail', {id: this.menu.id}))
                    .then((data) => {
                        this.menu = data.menu;
                    });
            },
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
                for (let productKey in data) {
                    let productId = data[productKey];

                    this.selectedProducts.push({
                        product_id: productId,
                        product_group_id: this.productGroup.id,
                    });
                }
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
        },
    };
</script>

<style scoped>
</style>
