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
                case this.paymentStatuses.notPaid.id: return 'badge-warning';
                case this.paymentStatuses.paid.id: return 'badge-success';
                case this.paymentStatuses.timeout.id: return 'badge-danger';
                case this.paymentStatuses.hold.id: return 'badge-primary';
                case this.paymentStatuses.error.id: return 'badge-info';
                default: return 'badge-dark';
            }
        },
    }
};
</script>
