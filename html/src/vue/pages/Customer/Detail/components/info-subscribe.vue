<template>
    <div>
        Подписки
    </div>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'info-subscribe',
    props: ['id'],
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.subscribe', {id: this.id})).then(data => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>