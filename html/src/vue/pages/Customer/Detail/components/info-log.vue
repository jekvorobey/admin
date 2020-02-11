<template>
    <div>
        Логи
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'info-log',
    props: ['id'],
    computed: {
        ...mapGetters(['getRoute']),
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.log', {id: this.id})).then(data => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>