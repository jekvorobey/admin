<template>
    <layout-main back>
        <b-row class="mb-2">
            <b-col class="col-12 col-md-8 mb-2">
                <infopanel :model.sync="variantGroup"/>
            </b-col>
            <b-col class="col-12 col-md-4 mb-2">
                <kpi :model="variantGroup"/>
            </b-col>
        </b-row>

        <b-card no-body>
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <tab-products v-if="key === 'products'" :model.sync="variantGroup"/>
                    <tab-properties v-else-if="key === 'properties'" :model.sync="variantGroup"/>
                    <template v-else>
                        Заглушка
                    </template>
                </b-tab>
                <template v-slot:tabs-end>
                    <b-nav-item @click.prevent="showAllTabs = !showAllTabs" href="#">
                        <b>{{ showAllTabs ? '-' : '+' }}</b>
                    </b-nav-item>
                </template>
            </b-tabs>
        </b-card>
    </layout-main>
</template>

<script>
    import Infopanel from './components/infopanel.vue';
    import Kpi from './components/kpi.vue';
    import TabProducts from './components/tab-products.vue';
    import TabProperties from './components/tab-properties.vue';
    import tabsMixin from '../../../../mixins/tabs';

    export default {
        mixins: [tabsMixin],
        components: {
            Infopanel,
            Kpi,
            TabProducts,
            TabProperties,
        },
        props: {
            iVariantGroup: {},
        },
        data() {
            return {
                variantGroup: this.iVariantGroup,
            };
        },
        computed: {
            tabs() {
                let tabs = {};
                let i = 0;

                tabs.products = {i: i++, title: 'Товары'};
                tabs.properties = {i: i++, title: 'Характеристики'};

                return tabs;
            },
        },
    };
</script>
