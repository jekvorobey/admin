<template>
    <b-card>
        <h3 class="mb-4">{{ offer.name }}</h3>
        <table class="table table-sm">
            <thead>
            <tr>
                <th colspan="4">
                    Инфопанель
                    <button
                            @click="openStatusEditModal()"
                            class="btn btn-sm btn-secondary float-right"
                            :disabled="countedQty < 1"
                    >Изменить статус
                    </button>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th>ID оффера</th>
                <td colspan="2">{{ offerModel.id }}</td>
            </tr>
            <tr>
                <th>Мерчант</th>
                <td colspan="2">{{ offer.merchantName }}</td>
            </tr>
            <tr>
                <th>Название товара</th>
                <td colspan="2">{{ offerModel.name }}</td>
            </tr>
            <tr>
                <th>Дата создания</th>
                <td colspan="2">{{ datePrint(offerModel.created_at) }}</td>
            </tr>
            <tr>
                <th>Текущая цена</th>
                <td colspan="2">{{ preparePrice(offerModel.price) }} руб.</td>
            </tr>
            <tr>
              <th>Базовая цена</th>
              <td colspan="2">{{ preparePrice(offerModel.cost) }} руб.</td>
            </tr>
            <tr>
                <th>Текущий остаток</th>
                <td colspan="2">{{ countedQty }} шт.</td>
            </tr>
            <tr>
                <th>Статус</th>
                <td colspan="2">
                    <span class="badge" :class="statusClass">
                        {{ statusNames[offerModel.status] ?statusNames[offerModel.status] : 'N/A' }}
                    </span>
                </td>
            </tr>
            <tr v-if="displayDate">
                <th>Дата начала продажи</th>
                <td colspan="2">{{ datePrint(offerModel.sale_at) }}</td>
            </tr>
            </tbody>
        </table>

        <offer-status-edit-modal :model.sync="offerModel"/>
    </b-card>
</template>

<script>
    import Helpers from '../../../../../scripts/helpers';
    import OfferStatusEditModal from './offer-status-edit-modal.vue';

    export default {
        components: {
            OfferStatusEditModal,
        },
        props: {
            offer: Object,
            stocks: Array,
        },
        data() {
            return {
                offerModel: this.offer,
            }
        },
        methods: {
            openStatusEditModal() {
                this.$bvModal.show('offer-status-edit-modal');
            },
        },
        computed: {
            statusNames() {
                let statusNames = [];
                Object.values(this.offerAllSaleStatuses).forEach((val) => {
                    statusNames[val.id] = val.name;
                });
                return statusNames;
            },
            statusClass() {
                switch(this.offerModel.status) {
                    case this.offerAllSaleStatuses.onSale.id:
                        return 'badge-success';
                    case this.offerAllSaleStatuses.preOrder.id:
                        return 'badge-warning';
                    case this.offerAllSaleStatuses.outSale.id:
                        return 'badge-danger';
                    case this.offerAllSaleStatuses.availableSale.id:
                        return 'badge-primary';
                    case this.offerAllSaleStatuses.notAvailableSale.id:
                        return 'badge-secondary';
                }
            },
            countedQty() {
                return this.stocks.reduce((total, stock) => {
                    return total + stock.qty;
                }, 0);
            },
            displayDate() {
                return this.offerModel.sale_at
                    && this.offerModel.status
                    && this.offerCountdownSaleStatuses.includes(this.offerModel.status);
            },
        },
    }
</script>