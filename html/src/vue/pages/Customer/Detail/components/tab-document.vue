<template>
    <div>
        Документы
    </div>
</template>

<script>

export default {
    name: 'tab-document',
    props: ['id'],
    created() {
        //Services.showLoader();
        //Services.net().get(this.getRoute('customers.detail.log', {id: this.id})).then(data => {
        //    Services.hideLoader();
        //})
    }
};
</script>

<style scoped>

</style>