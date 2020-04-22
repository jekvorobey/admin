<template>
    <div class="d-flex justify-content-start align-content-stretch">
        <div class="shadow mt-3 mr-3 p-3">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>ID предложения мерчанта</th>
                        <th>ID товара</th>
                        <th>Название товара</th>
                        <th>Артикул товара</th>
                        <th>Старая цена предложения мерчанта</th>
                        <th>Новая цена предложения мерчанта</th>
                        <th>Статус изменения цены</th>
                        <th>Комментарий по изменению цены</th>
                        <th>
                            <template v-if="!claim.isProcessed">
                                <button class="btn btn-primary" v-if="isWorkStatus"
                                        @click="changePrice('accept')"
                                        title="Принять все необработанные изменения">Принять все</button><br>
                                <button class="btn btn-primary mt-1" v-if="isWorkStatus"
                                        @click="rejectPrice()"
                                        title="Отклонить все необработанные изменения">Отклонить все</button>
                            </template>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="offer in claim.payload.offers">
                        <td>{{ offer.offerId }}</td>
                        <td>{{ product(offer.offerId).id }}</td>
                        <td>
                            <a :href="getRoute('products.detail', {id: product(offer.offerId).id})" target="_blank">
                                {{ product(offer.offerId).name }}
                            </a>
                        </td>
                        <td>{{ product(offer.offerId).vendor_code }}</td>
                        <td>{{ offer.oldPrice }} руб</td>
                        <td>{{ offer.newPrice }} руб</td>
                        <td>
                            <span class="badge" :class="statusChangePriceClass(offer.status)">
                                {{ statusChangePrice(offer.status) }}<br>{{offer.updated_at}}
                            </span>
                        </td>
                        <td>{{ offer.comment }}</td>
                        <td>
                            <template v-if="!offer.status">
                                <button class="btn btn-primary" v-if="isWorkStatus"
                                        @click="changePrice('accept', offer.offerId)">Принять</button><br>
                                <button class="btn btn-primary mt-1" v-if="isWorkStatus"
                                        @click="rejectPrice(offer.offerId)">Отклонить</button>
                            </template>
                        </td>
                    </tr>
                    <tr v-if="!claim.payload.offers">
                        <td colspan="7">Предложений мерчанта нет</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <offer-price-reject-modal
                :offer-id="selectedOffer"
                @onSave="rejectPriceSave"
                modal-name="offerPriceReject">
        </offer-price-reject-modal>
    </div>
</template>

<script>
    import Services from '../../../../../../scripts/services/services';
    import OfferPriceRejectModal from './offer-price-reject-modal.vue';
    import modalMixin from '../../../../../mixins/modal';

    export default {
        components: {OfferPriceRejectModal},
        props: [
            'model',
        ],
        data() {
            return {
                'selectedOffer': 0,
            }
        },
        mixins: [modalMixin],
        methods: {
            isStatus(statusId) {
                return this.claim.status === statusId;
            },
            product(offerId) {
                return this.claim.productsByOffers[offerId].product;
            },
            statusChangePriceClass(status) {
                switch (status) {
                    case 'accept': return 'badge-success';
                    case 'reject': return 'badge-warning';
                    default: return 'badge-secondary';
                }
            },
            statusChangePrice(status) {
                switch (status) {
                    case 'accept': return 'Изменена';
                    case 'reject': return 'Не изменена';
                    default: return 'Нет решения';
                }
            },
            rejectPrice(offerId = 0) {
                this.selectedOffer = offerId;
                this.openModal('offerPriceReject');
            },
            rejectPriceSave(data) {
                this.changePrice('reject', data.offerId, data.comment);
            },
            changePrice(action, offerId = 0, comment = '') {
                let errorMessage = 'Ошибка при изменении ' + (offerId ? 'цены.' : 'цен.');
                let data = {'action': action};
                if (offerId) {
                    data['offerId'] = offerId;
                }
                if (comment) {
                    data['comment'] = comment;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('priceChangeClaims.changePrice', {id: this.claim.id}), null, data).then(response => {
                    if (response.result === 'ok') {
                        this.claim.payload = response.claimPayload;
                        Services.msg("Изменения сохранены");
                    } else {
                        Services.msg(errorMessage + ' ' + response.error, 'danger');
                    }
                }, () => {
                    Services.msg(errorMessage, 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        computed: {
            claim: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
            isWorkStatus() {
                return this.isStatus(2);
            },
        },
};
</script>
<style scoped>
    th {
        vertical-align: top !important;
    }
</style>