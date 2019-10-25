<template>
    <div class="row mt-3">
        <div class="col-4">
            <div class="shadow p-3 w-100">
                123
            </div>
        </div>
        <div class="col-4">
            <div class="shadow p-3 w-100">
                123
            </div>
        </div>
        <div class="col-4">
            <div class="shadow p-3 w-100">
                123
            </div>
        </div>
    </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
    data() {
        return {

        }
    },
    props: {
    },
    methods: {
    },
    computed: {
        ...mapGetters(['getRoute']),
    },
    mounted() {
    }
}
</script>

<style scoped>

</style>
