<template>
    <layout-main back>
        <b-card no-body>
            <b-tabs lazy card v-model="tabIndex">
                <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
                    <tab-main v-if="key === 'main'"/>
                    <tab-ct v-else-if="key === 'ct'"/>
                    <tab-ppt v-else-if="key === 'ppt'"/>
                    <tab-pct v-else-if="key === 'pct'"/>
                </b-tab>
            </b-tabs>
        </b-card>
    </layout-main>
</template>

<script>
    import TabMain from './components/tab-main.vue';
    import TabCt from './components/tab-ct.vue';
    import TabPpt from './components/tab-ppt.vue';
    import TabPct from './components/tab-pct.vue';
    import tabsMixin from "../../../../mixins/tabs";

    export default {
        mixins: [tabsMixin],
        components: {
            TabMain,
            TabCt,
            TabPpt,
            TabPct,
        },
        computed: {
            tabs() {
                let tabs = {};
                let i = 0;

                tabs.main = {i: i++, title: 'Общие настроки'};
                tabs.ct = {i: i++, title: 'CT для мерчантов'};
                tabs.ppt = {i: i++, title: 'PPT для мерчантов'};
                tabs.pct = {i: i++, title: 'PCT для логистических операторов'};

                return tabs;
            },
        },
    };
</script>
