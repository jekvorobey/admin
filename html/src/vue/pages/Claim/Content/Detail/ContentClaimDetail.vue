<template>
    <layout-main back :back-url="backUrl">
        <infopanel
                :claim="claim"
                :options="options"
                @onMessageSave="onMessageSave"
        ></infopanel>

        <v-tabs :current="nav.currentTab" :items="nav.tabs" @nav="tab => nav.currentTab = tab"></v-tabs>
        <products-tab
                v-if="nav.currentTab === 'products'"
                :products="products"
        ></products-tab>
        <logs-tab
                v-if="nav.currentTab === 'history'"
                :history="history"
                :history-meta="historyMeta"
                :role-names="roleNames"
        ></logs-tab>
    </layout-main>
</template>

<script>
    import VTabs from '../../../../components/tabs/tabs.vue';
    import Infopanel from '../components/infopanel.vue';
    import ProductsTab from '../components/tab-products.vue';
    import LogsTab from '../components/tab-logs.vue';
    import Services from "../../../../../scripts/services/services";

    export default {
        components: {
            VTabs,
            Infopanel,
            ProductsTab,
            LogsTab
        },
        props: {
            claim: {},
            options: {},
            products: Array,
            history: Array,
            historyMeta: {},
            roleNames: {}
        },
        data() {
            return {
                backUrl: this.route('contentClaims.list'),
                nav: {
                    currentTab: 'products',
                    tabs: [
                        {value: 'products', text: 'Товары'},
                        {value: 'history', text: 'История'},
                    ]
                }
            };
        },
        methods: {
            onMessageSave(result) {
                Services.msg("Комментарий сохранен!");
                window.location.reload();
            }
        }
    };
</script>

<style scoped>
    .product-header {
        min-height: 200px;
    }
    .product-header > div {
        padding: 16px;
    }
    .product-header img {
        max-height: calc( 200px - 16px * 2 );
    }
    .product-header p {
        margin: 0;
        padding: 0;
    }
    .measure {
        width: 30px;
        margin-left: 10px;
    }
</style>
