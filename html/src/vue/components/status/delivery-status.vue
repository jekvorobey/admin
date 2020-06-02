<template>
    <span class="badge" :class="statusClass(status.id)">{{ status.name || 'N/A' }}</span>
</template>

<script>
export default {
    name: 'delivery-status',
    props: ['status'],
    methods: {
        statusClass(statusId) {
            switch (statusId) {
                case this.deliveryStatuses.preOrder.id: return 'badge-light';
                case this.deliveryStatuses.created.id: return 'badge-primary';
                case this.deliveryStatuses.awaitingCheck.id: return 'badge-warning';
                case this.deliveryStatuses.checking.id: return 'badge-warning';
                case this.deliveryStatuses.awaitingConfirmation.id: return 'badge-info';
                case this.deliveryStatuses.assembling.id: return 'badge-dark';
                case this.deliveryStatuses.assembled.id: return 'badge-dark';
                case this.deliveryStatuses.shipped.id: return 'badge-dark';
                case this.deliveryStatuses.onPointIn.id: return 'badge-info';
                case this.deliveryStatuses.arrivedAtDestinationCity.id: return 'badge-info';
                case this.deliveryStatuses.onPointOut.id: return 'badge-info';
                case this.deliveryStatuses.readyForRecipient.id: return 'badge-info';
                case this.deliveryStatuses.delivering.id: return 'badge-info';
                case this.deliveryStatuses.done.id: return 'badge-success';
                case this.deliveryStatuses.cancellationExpected.id: return 'badge-secondary';
                case this.deliveryStatuses.returnExpectedFromCustomer.id: return 'badge-secondary';
                case this.deliveryStatuses.returned.id: return 'badge-danger';
                default: return 'badge-dark';
            }
        },
    }
};
</script>
