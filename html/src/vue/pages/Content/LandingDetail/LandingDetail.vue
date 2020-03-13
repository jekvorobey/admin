<template>
    <layout-main back>
        <landing-constructor :iLanding = "iLanding"
                             :iWidgetsList = "iWidgetsList"
                             :iAllWidgetsNames = "iAllWidgetsNames"
        >
        </landing-constructor>
    </layout-main>
</template>

<script>
    import LandingConstructor from "./components/LandingConstructor/LandingConstructor.vue";

    export default {
        components: {
            LandingConstructor,
        },
        props: [
            'iLanding',
            'iWidgetsList',
            'iAllWidgetsNames',
        ],
        data() {
            return {};
        },
        methods: {
        },
        computed: {
        },
        watch: {
        },
        created() {
        },
        mounted() {
        },
    }
</script>
