<template>
    <span class="badge" :class="statusClass(status.id)">{{ status.name || 'N/A' }}</span>
</template>

<script>
export default {
    name: 'payment-status',
    props: ['status'],
    methods: {
        statusClass(statusId) {
            switch (statusId) {
                case 1: return 'badge-warning';
                case 2: return 'badge-success';
                case 3: return 'badge-danger';
                case 4: return 'badge-primary';
                default: return 'badge-dark';
            }
        },
    }
};
</script>
