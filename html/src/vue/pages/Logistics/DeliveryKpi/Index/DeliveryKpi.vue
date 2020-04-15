<template>
    <layout-main back>
        <b-card no-body>
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <tab-main v-if="key === 'main'"/>
                    <tab-ct v-else-if="key === 'ct'"/>
                    <template v-else>
                        Заглушка
                    </template>
                </b-tab>
            </b-tabs>
        </b-card>
    </layout-main>
</template>

<script>
    import TabMain from './components/tab-main.vue';
    import TabCt from './components/tab-ct.vue';
    import tabsMixin from "../../../../mixins/tabs";

    export default {
        mixins: [tabsMixin],
        components: {
            TabMain,
            TabCt,
        },
        computed: {
            tabs() {
                let tabs = {};
                let i = 0;

                tabs.main = {i: i++, title: 'Общие настроки'};
                tabs.ct = {i: i++, title: 'CT для мерчантов'};

                return tabs;
            },
        },
    };
</script>
