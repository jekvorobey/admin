<template>
    <layout-main back>

    </layout-main>
</template>

<script>
    import {mapActions, mapGetters} from "vuex";
    import Services from "../../../../scripts/services/services";
    import FileInput from '../../../components/controls/FileInput/FileInput.vue';

    export default {
        components: {
            FileInput
        },
        props: {
            iLanding: {},
            options: {}
        },
        data() {
            return {
                landing: this.iLanding,
            };
        },

        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
        },
        computed: {
            ...mapGetters(['getRoute']),
        },
    };
</script>

<style scoped>

</style>
