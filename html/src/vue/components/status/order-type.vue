<template>
    <span>
        <span class="badge badge-primary" v-if="type == basketTypes.product">Товары</span>
        <span class="badge badge-info" v-if="type == basketTypes.master">Мастер-классы</span>
    </span>
</template>

<script>
export default {
    name: 'order-type',
    props: ['type'],
};
</script>
