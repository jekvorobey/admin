<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="offerId" class="col-md-2 col-sm-12">ID предложения</f-input>
                    <f-input v-model="merchantId" class="col-md-2 col-sm-12">Мерчант</f-input>
                    <f-input v-model="status" class="col-md-2 col-sm-12">Статус</f-input>
                </div>
                <div class="row">
                    <f-input v-model="priceFrom" class="col-md-2 col-sm-12">
                        Цена от
                        <template #append><span class="input-group-text">руб.</span></template>
                    </f-input>
                    <f-input v-model="priceTo" class="col-md-2 col-sm-12">
                        Цена до
                        <template #append><span class="input-group-text">руб.</span></template>
                    </f-input>
                        <f-input v-model="qtyFrom" class="col-md-2 col-sm-12">
                        Остаток от
                        <template #append><span class="input-group-text">руб.</span></template>
                    </f-input>
                        <f-input v-model="qtyTo" class="col-md-2 col-sm-12">
                        Остаток до
                        <template #append><span class="input-group-text">руб.</span></template>
                    </f-input>
                </div>
            </div>
        </div>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>id</th>
                    <th>xml_id</th>
                    <th>Дата создания</th>
                    <th>Мерчант</th>
                    <th>Цена</th>
                    <th>Суммарный остаток <fa-icon icon="question-circle" v-b-popover.hover="tooltipQty"></fa-icon></th>
                    <th>Статус</th>
                    <th>Ручная сортировка</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(offer, index) in offers"
                        :class="offer.id === currentOffer.id ? 'table-primary' : ''"
                        :title="offer.id === currentOffer.id ? 'Оффер на витрине' : ''">
                    <td>
                        <a :href="getRoute('offers.detail', {id: offer.id})" target="_blank">
                            {{ offer.id }}
                        </a>
                    </td>
                    <td>
                        {{ offer.xml_id }}
                    </td>
                    <td>{{ offer.created_at }}</td>
                    <td>
                        <a :href="getRoute('merchant.detail', {id: offer.merchant.id})" target="_blank">
                            {{ offer.merchant.name }}
                        </a>
                    </td>
                    <td>{{ offer.price }} руб.</td>
                    <td>{{ offer.qty }} шт.</td>
                    <td>{{ offer.saleStatus.name }}</td>
                    <td>{{ offer.manual_sort }}</td>
                    <td>
                      <button class="btn btn-light btn-sm" @click="editOffer(index)" v-if="canUpdate(blocks.products)">
                        <fa-icon icon="pencil-alt"></fa-icon>
                      </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <offer-edit-modal
                title="Редактировать предложение"
                :offer = "this.offerModal"
                :saleOptions = "this.saleOptions"
                modal-name="offerEdit"
                @onSave="$emit('onSave')"/>
    </div>
</template>

<script>
import FInput from '../../../../components/filter/f-input.vue';
import Modal from '../../../../components/controls/modal/modal.vue';
import modalMixin from '../../../../mixins/modal';
import offerEditModal from './offer-edit-modal.vue';

export default {
        components:{
            FInput,
            offerEditModal,
            Modal,
        },
        mixins: [modalMixin],
        data: function() {
            return {
                offerId: '',
                merchantId: '',
                priceFrom: '',
                priceTo: '',
                qtyFrom: '',
                qtyTo: '',
                status: '',
                offerModal: null,
            }
        },
        props: {
            offers: Array,
            currentOffer: {},
            options: {},
        },
        methods: {
            editOffer: function (id) {
                this.offerModal = this.offers[id];
                this.openModal('offerEdit');
            },
        },
        computed: {
            saleOptions() {
                return Object.values(this.options.offerSaleStatuses).map(status => ({
                    value: status.id,
                    text: status.name
                }));
            },
            tooltipQty() {
                return 'Остаток оффера по всем складам мерчанта. На витрине выводятся остатки только с одного склада, где их больше всего';
            }
        }
    }
</script>

<style scoped>

</style>
