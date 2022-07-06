<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
                <button @click="toggleHiddenFilter" class="btn btn-sm btn-light float-right">
                    {{ opened ? 'Меньше' : 'Больше' }} фильтров
                    <fa-icon :icon="opened ? 'compress-arrows-alt' : 'expand-arrows-alt'"></fa-icon>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.id"  class="col-6">
                        ID оффера
                    </f-input>
                    <f-input v-model="filter.product_name" class="col-6">
                        Название оффера
                    </f-input>
                </div>
                <transition name="slide">
                    <div v-if="opened" class="additional-filter pt-3 mt-3">
                        <div class="row">
                            <f-multi-select v-model="filter.sale_status" :options="saleStatusOptions" class="col-6">
                                Статус оффера
                            </f-multi-select>
                            <f-input v-model="filter.price_from" type="number" class="col-3">
                                Текущая цена оффера
                                <template #prepend><span class="input-group-text">от</span></template>
                                <template #append><span class="input-group-text">руб.</span></template>
                            </f-input>
                            <f-input v-model="filter.price_to" type="number" class="col-3">
                                &nbsp;
                                <template #prepend><span class="input-group-text">до</span></template>
                                <template #append><span class="input-group-text">руб.</span></template>
                            </f-input>
                        </div>
                        <div class="row">
                            <f-input v-model="filter.qty_from" type="number" class="col-3">
                                Текущий остаток оффера
                                <template #prepend><span class="input-group-text">от</span></template>
                                <template #append><span class="input-group-text">шт.</span></template>
                            </f-input>
                            <f-input v-model="filter.qty_to" type="number" class="col-3">
                                &nbsp;
                                <template #prepend><span class="input-group-text">до</span></template>
                                <template #append><span class="input-group-text">шт.</span></template>
                            </f-input>
                            <f-date v-model="filter.created_at" class="col-6" range confirm>
                                Дата создания оффера
                            </f-date>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>

        <div class="row mb-3" v-if="canUpdate(blocks.products)">
            <div class="col-12 mt-3">
                <button class="btn btn-secondary" :disabled="countSelected < 1" @click="changeStatus()">Сменить статус
                    {{ pluralForm(countSelected, formsGenitive) }}
                </button>

                <button class="btn btn-secondary" disabled v-if="countSelected !== 1">Редактировать оффер</button>
                <a class="btn btn-warning" v-else>Редактировать оффер</a>
<!--                <a :href="getRoute('offer.edit', {id: selectedOffers[0].id})" class="btn btn-warning" v-else>Редактировать оффер</a>-->

                <button class="btn btn-danger" :disabled="countSelected < 1" @click="deleteOffer()">Удалить
                    {{ pluralForm(countSelected, formsDelete) }}
                </button>
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           id="select-all-page-shipments"
                           v-model="selectAll"
                           @click="changeSelectAll()"
                    >
                    <label for="select-all-page-shipments" class="mb-0">Все</label>
                </th>
                <th v-for="column in columns" v-if="column.isShown">{{column.name}}</th>
                <th>
                    <button class="btn btn-light float-right" @click="showChangeColumns">
                        <fa-icon icon="cog"></fa-icon>
                    </button>
                    <modal-columns :i-columns="editedShowColumns"></modal-columns>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="offer in offers">
                <td><input type="checkbox"
                           value="true"
                           class="offer-select"
                           v-model="checkboxes[offer.id]"
                           :value="offer.id"
                ></td>
                <td v-for="column in columns" v-if="column.isShown" v-html="column.value(offer)"></td>
            </tr>
            <tr v-if="!offers.length">
                <td :colspan="columns.length + 1">Товары отсутствуют</td>
            </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="pager.pages > 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                :hide-goto-end-buttons="pager.pages < 10"
                class="float-right"
        ></b-pagination>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('UpdateStatusOffer')">
                <div slot="header">
                    <b>Обновление статуса</b>
                </div>
                <div slot="body">
                    <div v-for="offer in selectedOffers">#{{ offer.id }} {{ offer.product.name }}</div>
                    <v-select v-model="newStatus" :options="saleStatusOptions" class="mt-3">Новый статус</v-select>
                    <button class="btn btn-success mt-3" type="button" @click="approveChangeStatus()">Изменить статус</button>
                </div>
            </modal>
        </transition>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('DeleteOffer')">
                <div slot="header">
                    <b>Вы уверены, что хотите удалить следующие предложения?</b>
                </div>
                <div slot="body">
                    <div v-for="offer in selectedOffers">#{{ offer.id }} {{ offer.product.name }}</div>
                    <button class="btn btn-danger mt-3" type="button" @click="approveDelete()">Удалить</button>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import FInput from '../../../../components/filter/f-input.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
    import FDate from '../../../../components/filter/f-date.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';

    import Services from "../../../../../scripts/services/services";

    import modalMixin from '../../../../mixins/modal';
    import modal from '../../../../components/controls/modal/modal.vue';
    import ModalColumns from '../../../../components/modal-columns/modal-columns.vue';

    const cleanHiddenFilter = {
        sale_status: [],
        price_from: null,
        price_to: null,
        qty_from: null,
        qty_to: null,
        created_at: [],
    };

    const cleanFilter = Object.assign({
        id: null,
        product_name: '',
    }, cleanHiddenFilter);

    const serverKeys = [
        'id',
        'product_name',
        'sale_status',
        'price_from',
        'price_to',
        'qty_from',
        'qty_to',
        'created_at',
    ];

    const formsGenitiveConst  = [
        "оффера",
        "офферов",
        "офферов"
    ];

    const formsDeleteConst  = [
        "оффер",
        "офферы",
        "офферы"
    ];

    export default {
        name: 'tab-product',
        props: ['id'],
        components: {
            FInput,
            FMultiSelect,
            FDate,
            ModalColumns,
            modal,
            VSelect,
        },
        mixins: [modalMixin],
        data() {
            let self = this;
            let filter = Object.assign({}, cleanFilter);

            return {
                opened: false,
                filter,
                appliedFilter: {},
                offerSaleStatuses: {},
                selectAll: false,
                currentPage: 1,
                pager: {},
                checkboxes: {},
                newStatus: 0,
                formsGenitive: formsGenitiveConst,
                formsDelete: formsDeleteConst,
                columns: [
                    {
                        name: 'ID оффера',
                        code: 'id',
                        value: function(offer) {
                            return offer.id;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Название оффера',
                        code: 'product_name',
                        value: function(offer) {
                            return '<a href="' + self.getRoute('products.detail', {id: offer.product.id}) + '">' +
                            offer.product.name + '</a>';
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Статус оффера',
                        code: 'sale_status',
                        value: function(offer) {
                            return '<span class="badge ' + self.statusClass(offer.sale_status.id) + '">' + offer.sale_status.name + '</span>';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Текущая цена оффера',
                        code: 'price',
                        value: function(offer) {
                            return offer.price;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Текущий остаток товаров оффера',
                        code: 'qty',
                        value: function(offer) {
                            return offer.qty;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Дата создания оффера',
                        code: 'created_at',
                        value: function(offer) {
                            return offer.created_at;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                ],
                offers: [],
            }
        },
        created() {
            Services.showLoader();
            Promise.all([
                Services.net().get(
                    this.getRoute('merchant.detail.product.data', {id: this.id})
                ),
                this.paginationPromise(),
            ]).then(data => {
                this.offerSaleStatuses = data[0].offerSaleStatuses;
                this.offers = data[1].offers;
                this.pager = data[1].pager;
            }).finally(() => {
                Services.hideLoader();
            });
        },
        methods: {
            toggleHiddenFilter() {
                this.opened = !this.opened;
                if (this.opened === false) {
                    for (let entry of Object.entries(cleanHiddenFilter)) {
                        this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                    }
                    this.applyFilter();
                }
            },
            forEachOffer(callback) {
                for (let i in this.offers) {
                    callback(this.offers[i]['id']);
                }
            },
            changeSelectAll() {
                let newValue = !this.selectAll;
                let checkboxes = {};
                this.forEachOffer((offerId) => {
                    checkboxes[offerId] = newValue;
                });
                this.checkboxes = checkboxes;
            },
            showChangeColumns() {
                this.openModal('list_columns');
            },
            statusClass(statusId) {
                switch (statusId) {
                    case 1: return 'badge-success';
                    case 2: return 'badge-info';
                    case 3: return 'badge-warning';
                    default: return 'badge-light';
                }
            },
            paginationPromise() {
                return Services.net().get(
                    this.getRoute('merchant.detail.product.pagination', {id: this.id}),
                    {
                        page: this.currentPage,
                        filter: this.appliedFilter,
                    }
                );
            },
            loadPage() {
                Services.showLoader();
                this.paginationPromise().then(data => {
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
                    if (value && serverKeys.indexOf(key) !== -1) {
                        tmpFilter[key] = value;
                    }
                }
                this.appliedFilter = tmpFilter;
                this.currentPage = 1;
                this.loadPage();
            },
            clearFilter() {
                for (let entry of Object.entries(cleanFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.applyFilter();
            },
            changeStatus() {
                this.openModal('UpdateStatusOffer');
            },
            deleteOffer() {
                this.openModal('DeleteOffer');
            },
            approveChangeStatus() {
                if (!this.newStatus) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.route('offers.change.saleStatus'), {
                    offer_ids: this.selectedIds,
                    sale_status: this.newStatus,
                }).then(() => {
                    this.closeModal('UpdateStatusOffer');
                    this.loadPage();
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            approveDelete() {
                Services.showLoader();
                Services.net().delete(this.route('offers.delete'), {
                    offer_ids: this.selectedIds,
                }).then(() => {
                    this.closeModal('DeleteOffer');
                    this.loadPage();
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        computed: {
            saleStatusOptions() {
                return Object.values(this.offerSaleStatuses).map(status => ({
                    value: status.id,
                    text: status.name
                }));
            },
            countSelected() {
                return Object.values(this.checkboxes).reduce((acc, val) => { return acc + val; }, 0);
            },
            selectedOffers() {
                return this.offers.filter((offer) => {
                    return (offer.id in this.checkboxes) && this.checkboxes[offer.id];
                });
            },
            selectedIds() {
                return this.offers.filter(offer => {
                    return (offer.id in this.checkboxes) && this.checkboxes[offer.id];
                }).map(offer => offer.id);
            },
            editedShowColumns() {
                return this.columns.filter(function(column) {
                    return !column.isAlwaysShown;
                })
            },
        },
        watch: {
            currentPage() {
                this.loadPage();
            }
        }
    };
</script>
<style scoped>
    .additional-filter {
        border-top: 1px solid #DFDFDF;
    }
</style>
