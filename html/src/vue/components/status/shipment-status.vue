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
                case this.shipmentStatuses.preOrder.id: return 'badge-light';
                case this.shipmentStatuses.created.id: return 'badge-primary';
                case this.shipmentStatuses.awaitingCheck.id: return 'badge-warning';
                case this.shipmentStatuses.checking.id: return 'badge-warning';
                case this.shipmentStatuses.awaitingConfirmation.id: return 'badge-info';
                case this.shipmentStatuses.assembling.id: return 'badge-dark';
                case this.shipmentStatuses.assembled.id: return 'badge-dark';
                case this.shipmentStatuses.shipped.id: return 'badge-dark';
                case this.shipmentStatuses.onPointIn.id: return 'badge-info';
                case this.shipmentStatuses.arrivedAtDestinationCity.id: return 'badge-info';
                case this.shipmentStatuses.onPointOut.id: return 'badge-info';
                case this.shipmentStatuses.readyForRecipient.id: return 'badge-info';
                case this.shipmentStatuses.delivering.id: return 'badge-info';
                case this.shipmentStatuses.done.id: return 'badge-success';
                case this.shipmentStatuses.cancellationExpected.id: return 'badge-secondary';
                case this.shipmentStatuses.returnExpectedFromCustomer.id: return 'badge-secondary';
                case this.shipmentStatuses.returned.id: return 'badge-danger';
                default: return 'badge-dark';
            }
        },
    }
};
</script>
