<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="merchantId" class="col-md-1 col-sm-12">Мерчант</f-input>
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
                    <f-input v-model="status" class="col-md-1 col-sm-12">Статус</f-input>
                </div>
            </div>
        </div>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Дата создания</th>
                    <th>Мерчант</th>
                    <th>Цена</th>
                    <th>Остаток</th>
                    <th>Статус</th>
                    <th>Ручная сортировка</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(offer, index) in filteredOffers" v-on:click="editeOffer(index)">
                    <td>{{ offer.offer_id }}</td>
                    <td>{{ offer.created_at }}</td>
                    <td>{{ offer.merchant_id }}</td>
                    <td>{{ offer.price }}</td>
                    <td>{{ offer.qty }}</td>
                    <td>{{ offer.sale_status }}</td>
                    <td>{{ offer.manual_sort }}</td>
                </tr>
            </tbody>
        </table>
        <offer-edit-modal
                title="Редактировать предложение"
                :offer = "this.offerModal"
                modal-name="offerEdit"
                @onSave="$emit('onSave')"/>
    </div>
</template>

<script>
    import FInput from "../../../../components/filter/f-input.vue";
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
        },
        methods: {
            headOffer: function () {
                return this.offers.find(offer => offer.sale_status === 1)
            },
            sortedOffers: function () {
                let items = this.offers
                    .sort((a,b) => {
                        return a.manual_sort - b.manual_sort;
                    })
                    .filter(offer => offer.sale_status !== 1);
                items.unshift(this.headOffer());
                return items;
            },
            editeOffer: function (id) {
                this.offerModal = this.offers[id];
                this.openModal('offerEdit');
            }
        },
        mounted() {
        },
        computed: {
            filteredOffers() {
                return this.sortedOffers()
                    .filter(offer => offer.merchant_id.toString().includes(this.merchantId))
                    .filter(offer => offer.sale_status.toString().includes(this.status))
                    .filter(offer => offer.price >= this.priceFrom)
                    .filter(offer => this.priceTo > 0 ? (offer.price <= this.priceTo) : true)
                    .filter(offer => offer.qty >= this.qtyFrom)
                    .filter(offer => this.qtyTo > 0 ? (offer.qty <= this.qtyTo) : true)
            }
        }
    }
</script>

<style scoped>

</style>
