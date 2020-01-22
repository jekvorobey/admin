<template>
    <layout-main back hide-title>
        delivery detail
    </layout-main>
</template>

<script>

import {mapGetters} from "vuex";

export default {
    components: {
    },
    props: {
    },
    data() {
        return {
        };
    },

    methods: {
    },
    computed: {
        ...mapGetters(['getRoute']),
    },
    mounted() {
    },
};
</script>
<style scoped>
</style>
