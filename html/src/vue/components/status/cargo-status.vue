<template>
    <span class="badge" :class="statusClass(status.id)">{{ status.name || 'N/A' }}</span>
</template>

<script>
export default {
    name: 'cargo-status',
    props: ['status'],
    methods: {
        statusClass(statusId) {
            switch (statusId) {
                case this.cargoStatuses.created.id: return 'badge-primary';
                case this.cargoStatuses.shipped.id: return 'badge-warning';
                case this.cargoStatuses.taken.id: return 'badge-success';
                default: return 'badge-dark';
            }
        },
    }
};
</script>
