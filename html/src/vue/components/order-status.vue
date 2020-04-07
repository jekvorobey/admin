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
                case 0: return 'badge-light';
                case 1: return 'badge-primary';
                case 2: return 'badge-warning';
                case 3: return 'badge-warning';
                case 4: return 'badge-info';
                case 5: return 'badge-dark';
                case 6: return 'badge-dark';
                case 7: return 'badge-dark';
                case 8: return 'badge-dark';
                case 9: return 'badge-success';
                case 10: return 'badge-danger';
                default: return 'badge-secondary';
            }
        },
    }
};
</script>

<style scoped>

</style>