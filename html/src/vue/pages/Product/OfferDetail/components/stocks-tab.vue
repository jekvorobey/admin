<template>
    <div>
        <div class="card mt-1">
            <div class="card-header">
                Фильтр
                <button @click="toggleFilter" class="btn btn-sm btn-light float-right">
                    {{ opened ? 'Свернуть' : 'Открыть' }} фильтр
                    <fa-icon :icon="opened ? 'compress-arrows-alt' : 'expand-arrows-alt'"></fa-icon>
                </button>
            </div>
            <div v-show="opened" class="card-body">
                <div class="row">
                    <f-input v-model="filter.name" class="col-md-2 col-sm-12">Название склада</f-input>
                    <f-input v-model="filter.address" class="col-md-3 col-sm-12">Адрес склада</f-input>
                    <f-input v-model="filter.contacts" class="col-md-3 col-sm-12">Контактное лицо</f-input>
                    <f-input v-model="filter.qty_from" type="number" min="0" class="col-sm-12 col-md-2">
                        Кол-во экземпляров
                        <template #prepend><span class="input-group-text">от</span></template>
                        <template #append><span class="input-group-text">шт.</span></template>
                    </f-input>
                    <f-input v-model="filter.qty_to" type="number" min="0" class="col-sm-12 col-md-2">
                        &nbsp;
                        <template #prepend><span class="input-group-text">до</span></template>
                        <template #append><span class="input-group-text">шт.</span></template>
                    </f-input>
                </div>
            </div>
            <div v-show="opened" class="card-footer">
                <button class="btn btn-sm btn-dark" @click="applyFilter">Применить</button>
                <button class="btn btn-sm btn-outline-dark" @click="clearFilter">Очистить</button>
            </div>
        </div>

        <div class="row mb-3" v-if="canUpdate(blocks.products)">
            <div class="col-12 mt-3">
                <button class="btn btn-warning" @click="openStocksEditModal">Редактировать остатки</button>
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>Название склада</th>
                <th>Кол-во экземпляров</th>
                <th>Адрес склада</th>
                <th>Контактное лицо</th>
            </tr>
            </thead>
            <tbody v-for="(stock, index) in stocksList" class="table table-striped">
            <tr class="table">
                <td><a :href="getRoute('merchantStore.edit', {id: stock.store_id})">{{ stock.name }}</a></td>
                <td>{{ stock.qty }} шт.</td>
                <td>{{ stock.address }}</td>
                <td class="with-small">{{ stock.contacts ?
                    stock.contacts.name ? stock.contacts.name : 'N/A'
                    : 'Нет' }}
                    <small v-if="stock.contacts">{{ stock.contacts.phone ? stock.contacts.phone : 'N/A' }}</small>
                    <small v-if="stock.contacts">{{ stock.contacts.email ? stock.contacts.email : 'N/A' }}</small>
                </td>
            </tr>
            </tbody>
        </table>

        <offer-stocks-edit-modal :offer="offer" :stocks="stocks" @onSave="loadEditedStocks"/>
    </div>
</template>

<script>
    import FInput from "../../../../components/filter/f-input.vue";
    import FDate from '../../../../components/filter/f-date.vue';
    import Services from "../../../../../scripts/services/services";
    import OfferStocksEditModal from './offer-stocks-edit-modal.vue';

    const cleanFilter = {
        name: '',
        address: '',
        contacts: '',
        qty_from: '',
        qty_to: '',
    };

    export default {
        components:{
            FInput,
            FDate,
            OfferStocksEditModal,
        },
        props: {
            offer: Object,
            stocks: Array,
        },
        data() {
            return {
                filter: {
                    name: '',
                    address: '',
                    contacts: '',
                    qty_from: '',
                    qty_to: '',
                },
                opened: false,
                stocksList: this.stocks,
            }
        },
        methods: {
            loadEditedStocks() {
                this.appliedFilter = {};
                for (let entry of Object.entries(cleanFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.loadStocksPromise().then(data => {
                    this.stocksList = data.stocks;
                    this.$emit('onSave', this.stocksList);
                });
            },
            loadStocks() {
                Services.showLoader();
                this.loadStocksPromise().then(data => {
                    this.stocksList = data.stocks;
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            loadStocksPromise() {
                return Services.net().get(this.getRoute('offers.stocks', {id: this.offer.id}), {
                    filter: this.appliedFilter,
                });
            },
            toggleFilter() {
                this.opened = !this.opened;
                if (this.opened === false) {
                    this.clearFilter();
                    this.applyFilter();
                }
            },
            clearFilter() {
                for (let entry of Object.entries(cleanFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.applyFilter();
            },
            applyFilter() {
                let tmpFilter = {};
                for (let [key, value] of Object.entries(this.filter)) {
                    if ((!Array.isArray(value) && value || (Array.isArray(value) && value.length > 0))
                        && Object.keys(cleanFilter).indexOf(key) !== -1) {
                        tmpFilter[key] = value;
                    }
                }
                this.appliedFilter = tmpFilter;
                this.loadStocks();
            },
            openStocksEditModal() {
                this.$bvModal.show('offer-stocks-edit-modal');
            },
        },
    }
</script>

<style scoped>
    .with-small small{
        display: block;
        color: gray;
        line-height: 1rem;
        overflow: hidden;
    }
</style>
