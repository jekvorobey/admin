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
                case this.orderStatuses.preOrder.id: return 'badge-light';
                case this.orderStatuses.created.id: return 'badge-primary';
                case this.orderStatuses.awaitingCheck.id: return 'badge-warning';
                case this.orderStatuses.checking.id: return 'badge-warning';
                case this.orderStatuses.awaitingConfirmation.id: return 'badge-info';
                case this.orderStatuses.inProcessing.id: return 'badge-dark';
                case this.orderStatuses.transferredToDelivery.id: return 'badge-dark';
                case this.orderStatuses.delivering.id: return 'badge-dark';
                case this.orderStatuses.readyForRecipient.id: return 'badge-dark';
                case this.orderStatuses.done.id: return 'badge-success';
                case this.orderStatuses.returned.id: return 'badge-danger';
                default: return 'badge-dark';
            }
        },
    }
};
</script>
