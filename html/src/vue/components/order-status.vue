<template>
    <span class="badge" :class="statusClass(status.id)">{{ status.name || 'N/A' }}</span>
</template>

<script>
export default {
    name: 'order-status',
    props: ['status'],
    methods: {
        statusClass(statusId) {
            switch (statusId) {
                case 1: return 'badge-danger';
                case 2: return 'badge-warning';
                case 3: return 'badge-success';
                default: return 'badge-secondary';
            }
        },
    }
};
</script>

<style scoped>

</style>