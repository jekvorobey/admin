<template>
    <span class="badge" :class="statusClass(status.id)">{{ status.name || 'N/A' }}</span>
</template>

<script>
export default {
    name: 'shipment-status',
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
                case 21: return 'badge-info';
                case 22: return 'badge-info';
                case 23: return 'badge-info';
                case 24: return 'badge-info';
                case 25: return 'badge-info';
                case 26: return 'badge-success';
                case 41: return 'badge-secondary';
                case 42: return 'badge-secondary';
                case 43: return 'badge-danger';
                default: return 'badge-dark';
            }
        },
    }
};
</script>
