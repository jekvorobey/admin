<template>
    <div>

    </div>
</template>

<script>
    import Services from '../../../../../scripts/services/services';
    import {mapGetters} from 'vuex';

    export default {
        components: {},
        props: {
            iMenuItems: Array,
        },
        data() {
            return {
                selectedMenuItems: this.iMenuItems,
            };
        },
        methods: {
        },
        computed: {
            ...mapGetters(['getRoute']),
        },
        watch: {
        },
        mounted: function () {

        }
    }
</script>
