<template>
    <layout-main>
        <b-card>
            <template v-slot:header>
                Фильтр
                <button @click="toggleHiddenFilter" class="btn btn-sm btn-light float-right">
                    {{ opened ? 'Меньше' : 'Больше' }} фильтров
                    <fa-icon :icon="opened ? 'compress-arrows-alt' : 'expand-arrows-alt'"></fa-icon>
                </button>
            </template>
            <div class="row">
                <f-input v-model="filter.id" class="col-sm-12 col-md-2 col-xl-1">
                    ID оффера
                </f-input>
                <f-input v-model="filter.productName" class="col-sm-12 col-md-3 col-xl-3">
                    Наименование товара
                </f-input>
                <f-multi-select v-model="filter.statuses" :options="toOptionsArray(statusNames)" class="col-sm-12 col-md-3 col-xl-4">
                    Статус
                </f-multi-select>
                <f-input v-model="filter.price_from" type="number" min="0" class="col-sm-12 col-md-2">
                    Цена
                    <template #prepend><span class="input-group-text">от</span></template>
                    <template #append><span class="input-group-text">руб.</span></template>
                </f-input>
                <f-input v-model="filter.price_to" type="number" min="0" class="col-sm-12 col-md-2">
                    &nbsp;
                    <template #prepend><span class="input-group-text">до</span></template>
                    <template #append><span class="input-group-text">руб.</span></template>
                </f-input>
            </div>
            <transition name="slide">
                <div v-if="opened">
                    <div class="additional-filter pt-3 mt-3">
                        <div class="row">
                            <f-multi-select v-model="filter.merchants" :options="toOptionsArray(options.merchants)" class="col-sm-12 col-md-8 col-xl-8">
                                Мерчант
                            </f-multi-select>
                            <f-input v-model="filter.qty_from" type="number" min="0" class="col-sm-12 col-md-2">
                                Остаток
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
                </div>
            </transition>
            <template v-slot:footer>
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего предложений: {{ pager.total }}.</span>
            </template>
        </b-card>

        <div class="row mb-3" v-if="canUpdate(blocks.products)">
            <div class="col-12 mt-3">
                <button class="btn btn-success" @click="editOffer(null)">Добавить оффер</button>
                <button class="btn btn-warning" :disabled="countSelected < 1 || isEmptyStockOfferSelected()" @click="editOfferStatus()">Изменить статус
                    <template v-if="countSelected <= 1">оффера</template>
                    <template v-else>офферов</template>
                </button>
                <button class="btn btn-primary" :disabled="countSelected !== 1" @click="editOffer(selectedOffers[0])">Редактировать оффер</button>
            </div>
        </div>

        <table class="table" v-if="offers && offers.length > 0">
            <thead>
                <tr>
                    <th><input type="checkbox" v-model="selectAll" @click="changeSelectAll()"></th>
                    <th>ID</th>
                    <th>Товар</th>
                    <th>Мерчант</th>
                    <th>Статус продажи</th>
                    <th>Цена</th>
                    <th>Остаток</th>
                    <th>Дата создания</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(offer, index) in offers">
                    <td><input type="checkbox" v-model="checkboxes[offer.id]"></td>
                    <td><a :href="getRoute('offers.detail', {id: offer.id})">{{ offer.id }}</a></td>
                    <td>{{ offer.productName }}</td>
                    <td>{{ offer.merchantName }}</td>
                    <td>
                        <span class="badge" :class="getStatusClass(offer.saleStatus)">
                        {{ statusNames[offer.saleStatus] ? statusNames[offer.saleStatus] : 'N/A' }}
                        </span>
                    </td>
                    <td>{{ formatPrice(offer.price) }}</td>
                    <td>{{ formatQty(offer.qty) }}</td>
                    <td>{{ datePrint(offer.createdAt) }}</td>
                </tr>
            </tbody>
        </table>
        <p v-else class="text-center p-3">Ничего не найдено!</p>
        <offer-create-modal
                :offer = "offerModal"
                :merchants = "options.merchants"/>
        <offer-status-edit-modal
                :offers = "offersModal"/>
        <div>
            <b-pagination
                    v-if="pager.pages > 1"
                    v-model="currentPage"
                    :total-rows="pager.total"
                    :per-page="pager.pageSize"
                    @change="changePage"
                    :hide-goto-end-buttons="pager.pages < 10"
                    class="mt-3 float-right"
            ></b-pagination>
        </div>
    </layout-main>
</template>

<script>

    import Services from '../../../../scripts/services/services';
    import withQuery from 'with-query';

    import FMultiSelect from '../../../components/filter/f-multi-select.vue';
    import FInput from '../../../components/filter/f-input.vue';
    import Modal from '../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../mixins/modal';
    import offerCreateModal from './components/offer-create-modal.vue';
    import offerStatusEditModal from './components/offer-status-edit-modal.vue';

    const cleanHiddenFilter = {
        merchants: [],
        qty_from: '',
        qty_to: '',
    };

    const cleanFilter = Object.assign({
        id: '',
        productName: '',
        statuses: [],
        price_from: '',
        price_to: '',
    }, cleanHiddenFilter);

    export default {
        components: {
            FMultiSelect,
            FInput,
            Modal,
            offerCreateModal,
            offerStatusEditModal,
        },
        mixins: [modalMixin],
        props: {
            iOffers: {},
            iPager: {},
            iCurrentPage: {},
            iFilter: {},
            options: {},
        },
        data() {
            let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
            return {
                offers: this.iOffers,
                pager: this.iPager,
                currentPage: this.iCurrentPage || 1,
                filter,
                offerModal: null,
                offersModal: null,
                opened: false,
                checkboxes: {},
                selectAll: false,
            };
        },
        methods: {
            changePage(newPage) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: newPage,
                    filter: this.appliedFilter,
                    //sort: this.sort
                }));
            },
            loadPage() {
                Services.showLoader();
                Services.net().get(this.route('offers.listPage'), {
                    page: this.currentPage,
                    filter: this.appliedFilter,
                    //sort: this.sort,
                }).then(data => {
                    this.checkboxes = {};
                    this.offers = data.offers;
                    if (data.pager) {
                        this.pager = data.pager
                    }
                }).finally(() => {
                    Services.hideLoader();
                });
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
                this.currentPage = 1;
                this.changePage(1);
                this.loadPage();
            },
            toggleHiddenFilter() {
                this.opened = !this.opened;
                if (this.opened === false) {
                    for (let entry of Object.entries(cleanHiddenFilter)) {
                        this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                    }
                    this.applyFilter();
                }
            },
            clearFilter() {
                for (let entry of Object.entries(cleanFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.applyFilter();
            },
            editOffer(offer) {
                if (!offer) {
                    this.offerModal = null;
                } else {
                    this.offerModal = {
                        id: offer.id,
                        product_id: offer.productId,
                        merchant_id: offer.merchantId,
                        price: offer.price,
                        stocks: [],
                        status: offer.saleStatus,
                        sale_at: offer.saleAt
                    };
                }
                this.$bvModal.show('offer-create-modal');
            },
            editOfferStatus() {
                this.offersModal = this.selectedOffers;
                this.$bvModal.show('offer-status-edit-modal');
            },
            toOptionsArray(options) {
                return Object.entries(options).map(([k,v]) => {
                    return {value: parseInt(k), text: v};
                });
            },
            formatQty(qty) {
                return qty + ' шт.';
            },
            formatPrice(price) {
                return this.preparePrice(price) + ' руб.';
            },
            getStatusClass(statusId) {
                switch(statusId) {
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
            changeSelectAll() {
                let selected = !this.selectAll;
                let checkboxes = {};
                for (let offer of this.offers) {
                    checkboxes[offer.id] = selected;
                }
                this.checkboxes = checkboxes;
            },
            isEmptyStockOfferSelected() {
                return this.selectedOffers.some((offer) => {
                    return !offer.qty;
                })
            }
        },
        created() {
            window.onpopstate = () => {
                let query = qs.parse(document.location.search.substr(1));
                if (query.page) {
                    this.currentPage = query.page;
                }
            };
        },
        watch: {
            currentPage() {
                this.loadPage();
            }
        },
        computed: {
            statusNames() {
                let statusNames = [];
                Object.values(this.offerAllSaleStatuses).forEach((val) => {
                    statusNames[val.id] = val.name;
                });
                return statusNames;
            },
            countSelected() {
                return Object.values(this.checkboxes).reduce((acc, val) => acc + val, 0);
            },
            selectedOffers() {
                return this.offers.filter(offer => {
                    return (offer.id in this.checkboxes) && this.checkboxes[offer.id];
                });
            },
            selectedIds() {
                return this.offers.filter(offer => {
                    return (offer.id in this.checkboxes) && this.checkboxes[offer.id];
                }).map(offer => offer.id);
            },
        }
    };
</script>
