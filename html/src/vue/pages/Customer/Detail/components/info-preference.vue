<template>
    <div>
        Предпочтения
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'info-preference',
    props: ['id'],
    computed: {
        ...mapGetters(['getRoute']),
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.preference', {id: this.id})).then(data => {
            Services.event().$emit('kpiLoad', {kpis: data.kpis});
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>