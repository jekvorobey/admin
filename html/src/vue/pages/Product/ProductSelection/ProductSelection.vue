<template>
    <layout-main>
        <b-card>
            <div class="row">
                <f-multi-select v-model="filter.merchants" :options="toOptionsArray(options.merchants)" class="col-sm-12 col-md-8 col-xl-8">
                    Мерчант
                </f-multi-select>
            </div>
            <div class="row">
                <f-text-area v-if="this.isOfferId" placeholderName="Введите Offer ID через запятую" v-model="filter.offerId" class="col-sm-12 col-md-8 col-xl-8"></f-text-area>
                <f-text-area v-else placeholderName="Введите Артикулы через запятую" v-model="filter.vendorCode" class="col-sm-12 col-md-8 col-xl-8"></f-text-area>
                <button class="switch-btn btn btn-dark" @click="changeTextFilter">
                    <span class="font-weight-bold">{{this.isOfferId ? 'Поиск по Артикулам' : 'Поиск по Offer ID'}}</span>
                </button>
            </div>
            <template v-slot:footer>
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего товаров: {{ total }}.</span>
            </template>
        </b-card>

        <div class="d-flex justify-content-between mt-3 mb-3">
            <div class="action-bar d-flex justify-content-start">
                <span class="total-products mr-4">Выбрано товаров: {{massAll(massProductsType).length}}</span>
                <button v-if="!massEmpty(massProductsType)"
                        @click="copyOfferIdsToClipBoard"
                        type="button"
                        class="btn btn-outline-secondary mr-3">
                    <fa-icon icon="copy" class="mr-1"></fa-icon>
                    <span>Копировать ID офферов</span>
                </button>
                <button v-if="!massEmpty(massProductsType)"
                        @click="copyArticlesToClipBoard"
                        type="button"
                        class="btn btn-outline-secondary mr-3">
                    <fa-icon icon="copy" class="mr-1"></fa-icon>
                    <span>Копировать артикулы</span>
                </button>
                <button v-if="!massEmpty(massProductsType)"
                        @click="exportOffersById"
                        type="button"
                        class="btn btn-outline-primary mr-3">
                    <fa-icon icon="download" class="mr-1"></fa-icon>
                    <span>Скачать Офферы</span>
                </button>
                <button v-if="!massEmpty(massProductsType)"
                        @click="exportArticlesById"
                        type="button"
                        class="btn btn-outline-primary">
                    <fa-icon icon="download" class="mr-1"></fa-icon>
                    <span>Скачать артикулы</span>
                </button>
            </div>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           :checked="checkAll"
                           @change="e => massCheckboxAllOnPage(e)"/>
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
            <tr v-for="product in products">
                <td class="d-flex flex-column align-items-center">
                    <input type="checkbox"
                           :checked="massHas({type:massProductsType, id:product.id})"
                           @change="e => massCheckbox(e, massProductsType, product.id)"/>
                </td>
                <td v-for="column in columns" v-if="column.isShown" v-html="column.value(product)"></td>
                <td></td>
            </tr>
            </tbody>
        </table>

        <div>
            <b-pagination
                    v-if="numPages !== 1"
                    v-model="page"
                    :total-rows="total"
                    :per-page="pageSize"
                    :hide-goto-end-buttons="numPages < 10"
                    class="mt-3 float-right"
            ></b-pagination>
        </div>
    </layout-main>
</template>

<script>
    import BulkOfferLoader, {
        mode as loaderMode,
        returnMode
    } from '../../../components/bulk-offer-loader/bulk-offer-loader.vue';
    import Services from "../../../../scripts/services/services";
    import FMultiSelect from '../../../components/filter/f-multi-select.vue';
    import ModalColumns from '../../../components/modal-columns/modal-columns.vue';
    import FInput from '../../../components/filter/f-input.vue';
    import FTextArea from '../../../components/filter/f-text-area.vue';

    import {
        ACT_LOAD_PAGE,
        ACT_LOAD_SELECTION_PAGE,
        ACT_UPDATE_APPROVAL,
        ACT_UPDATE_ARCHIVE,
        ACT_UPDATE_PRODUCTION,
        GET_LIST,
        GET_NUM_PAGES, GET_PAGE_NUMBER,
        GET_PAGE_SIZE, GET_TOTAL,
        NAMESPACE,
        SET_PAGE,
    } from '../../../store/modules/products';
    import qs from 'qs';
    import {mapActions, mapGetters, mapMutations} from "vuex";
    import withQuery from "with-query";
    import {SET_CLEAR, SET_DESELECT, SET_SELECT} from "../../../store/modules/mass-selection";
    import Helpers from "../../../../scripts/helpers";
    import Media from "../../../../scripts/media";

    import modalMixin from '../../../mixins/modal';
    import mediaMixin from '../../../mixins/media';
    import massSelectionMixin from '../../../mixins/mass-selection';
    import { validationMixin } from 'vuelidate';
    import { required } from 'vuelidate/lib/validators';
    import * as clipboard from "clipboard-polyfill";

    const cleanHiddenFilter = {
        merchants: [],
    };

    const cleanFilter = Object.assign({
        offerId : [],
        vendorCode: [],
    }, cleanHiddenFilter);

    export default {
        name: "ProductSelection",

        mixins: [
            modalMixin,
            mediaMixin,
            massSelectionMixin,
            validationMixin
        ],

        components: {
            BulkOfferLoader,
            FMultiSelect,
            ModalColumns,
            FInput,
            FTextArea
        },

        props: {
            iProducts: {},
            iTotal: {},
            iCurrentPage: {},
            iFilter: {},
            options: {},
            iSelectedProductIds: Array,
        },

        data() {
            this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
                list: this.iProducts,
                total: this.iTotal,
                page: this.iCurrentPage
            });

            let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
            filter.merchants = filter.merchants.map(value => parseInt(value));

            return {
                checkboxes: {},
                filter,
                selectedProductIds: this.iSelectedProductIds,
                offerLoaderReturnModes: returnMode,
                opened: false,
                massProductsType: 'products',
                isOfferId: true,
                columns: [
                    {
                        name: 'offer id',
                        code: 'offerId',
                        value: function(claim) {
                            return claim.offerId  ? claim.offerId : 'N/A';
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Мерчант',
                        code: 'merchantName',
                        value: function(claim) {
                            return claim.merchantName ? claim.merchantName : 'N/A';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Артикул',
                        code: 'vendorCode',
                        value: function(claim) {
                            return claim.vendorCode ? claim.vendorCode : 'N/A';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'product id',
                        code: 'id',
                        value: function(claim) {
                            return claim.id ? claim.id : 'N/A';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Товар',
                        code: 'name',
                        value: function(claim) {
                            return claim.name ? claim.name : 'N/A';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Бренд',
                        code: 'brandName',
                        value: (claim) => {
                            return claim.brandName ? claim.brandName : 'N/A';
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'На витрине',
                        code: 'active',
                        value: (claim) => {
                            return `<span class="badge ${claim.active ? 'badge-success' : 'badge-danger'}">` + (claim.active ? 'Да' : 'Нет') + '</span>'

                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                ],
            };
        },

        methods: {
            ...mapActions(NAMESPACE, [
                ACT_LOAD_PAGE,
                ACT_LOAD_SELECTION_PAGE,
                ACT_UPDATE_PRODUCTION,
                ACT_UPDATE_ARCHIVE,
                ACT_UPDATE_APPROVAL,
            ]),

            ...mapMutations(NAMESPACE, {
                massClear: SET_CLEAR
            }),

            changeTextFilter(){
                this.filter.offerId = null;
                this.filter.vendorCode = null;
                this.isOfferId = !this.isOfferId
            },

            imageUrl(id) {
                return Media.compressed(id, 290, 290);
            },

            productionName(status) {
                if (this.options.productionStatuses[status]) {
                    return this.options.productionStatuses[status].name;
                }
                return 'N/A';
            },

            approvalName(status) {
                if (this.options.approvalStatuses[status]) {
                    return this.options.approvalStatuses[status].name;
                }
                return 'N/A';
            },

            isProductionDone(status){
                return status === this.options.productionDone;
            },

            isApprovalDone(status) {
                return status === this.options.approvalDone;
            },

            roundValue(value) {
                return Helpers.roundValue(value)
            },

            formatDate(date) {
                return new Date(date).toLocaleDateString();
            },

            applyFilter() {
                this.loadPage(1);
                this.massClear(this.massProductsType);
            },

            clearFilter() {
                this.$set(this, 'filter', JSON.parse(JSON.stringify(cleanFilter)));
                this.applyFilter();
                this.massClear(this.massProductsType);
            },

            toOptionsArray(options) {
                return Object.entries(options).map(([k,v]) => {
                    return {value: parseInt(k), text: v};
                });
            },

            isHiddenFilterDefaultOpen() {
                for (let entry of Object.entries(cleanHiddenFilter)) {
                    if (!Helpers.isEqual(entry[1], this.filter[entry[0]]) && entry[1] !== this.filter[entry[0]]) {
                        return true;
                    }
                }
                return false;
            },

            async offerLoader(mode, codes) {
                const params = {};

                if (mode === loaderMode.OFFER_ID) {
                    params.id = codes;
                } else {
                    params.vendor_code = codes;
                }

                let offers = await Services.net().post(
                    mode === loaderMode.OFFER_ID
                        ? this.getRoute('productGroup.getProductsByOffers')
                        : this.getRoute('productGroup.getProducts'),
                    {},
                    params,
                );

                return offers.map(offer => {
                    return {
                        id: offer.id,
                        vendorCode: offer.vendor_code,
                        productId: offer.product ? offer.product.id : offer.id,
                    };
                });
            },

            onLoadOffers(ids) {
                const internalIds = [ ...this.selectedProductIds ];

                for (const id of ids) {
                    if (!internalIds.includes(id)) {
                        this.$emit('add', id);
                        internalIds.push(id);
                    }
                }

                this.selectedProductIds = [ ...internalIds ];
                this.fetchProducts(this.selectedProductIds);
            },

            loadPage(page) {
                let cleanFilter = {};
                for (let [key, value] of Object.entries(this.filter)) {
                    if (value !== undefined && value !== null && value !== '') {
                        cleanFilter[key] = value;
                    }
                }
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: page,
                    filter: cleanFilter,
                }));

                return this[ACT_LOAD_SELECTION_PAGE]({
                    page: page,
                    filter: cleanFilter
                });
            },

            exportOffersById() {
                Services.showLoader();

                let text = this.massAll(this.massProductsType)
                    .map(x => {
                        if(this.products.find(prod => prod.id === x) !== undefined) {
                            return this.products.find(prod => prod.id === x).offerId
                        }
                    }).join(' ');

                let cleanOfferIds = text.trim().split(' ')

                Services.net().get(
                    this.route('products.selection.exportByOfferIds'),
                    {'offer_ids': cleanOfferIds}
                ).then(data => {
                        let link = document.createElement("a");
                        link.href = data.path;
                        link.setAttribute('download','');
                        console.log(link)
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        link = null;

                        Services.msg("Офферы экспортированы");
                    },
                    () => {
                        Services.msg("Ошибка экспорта товаров", 'danger');
                    }
                ).finally(() => {
                    Services.hideLoader();
                });
            },
            exportArticlesById() {
                Services.showLoader();

                let text = this.massAll(this.massProductsType)
                    .map(x => {
                        if(this.products.find(prod => prod.id === x) !== undefined) {
                            return this.products.find(prod => prod.id === x).vendorCode
                        }
                    }).join(' ');

                let cleanVendorCodes = text.trim().split(' ')

                Services.net().get(
                    this.route('products.selection.exportByVendorCodes'),
                    {'vendor_codes': cleanVendorCodes}
                ).then(data => {
                        let link = document.createElement("a");
                        link.href = data.path;
                        link.setAttribute('download','');
                        console.log(link)
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        link = null;

                        Services.msg("Товары экспортированы");
                    },
                    () => {
                        Services.msg("Ошибка экспорта товаров", 'danger');
                    }
                ).finally(() => {
                    Services.hideLoader();
                });
            },

            massCheckboxAllOnPage(e) {
                if (e.target.checked) {
                    this.products.forEach(product => {
                        if (!this.massAll(this.massProductsType).includes(product.id)) {
                            this.massCheckbox(e, this.massProductsType, product.id);
                        }
                    });
                } else {
                    this.products.forEach(product => {
                        if (this.massAll(this.massProductsType).includes(product.id)) {
                            this.massCheckbox(e, this.massProductsType, product.id);
                        }
                    });
                }
            },

            copyOfferIdsToClipBoard() {
                let text = this.massAll(this.massProductsType)
                    .map(x => {
                        if(this.products.find(prod => prod.id === x) !== undefined) {
                            return this.products.find(prod => prod.id === x).offerId
                        }
                    }).join(' ');

                let cleanText = text.trim().split(' ').join(',')
                clipboard.writeText(cleanText).then();
            },

            copyArticlesToClipBoard() {
                let text = this.massAll(this.massProductsType)
                    .map(x => {
                        if(this.products.find(prod => prod.id === x) !== undefined) {
                            return this.products.find(prod => prod.id === x).vendorCode
                        }
                    }).join(' ');

                let cleanText = text.trim().split(' ').join(',')
                clipboard.writeText(cleanText).then();
            },

            showChangeColumns() {
                this.openModal('list_columns');
            },
        },

        computed: {
            ...mapGetters(NAMESPACE, {
                GET_PAGE_NUMBER,
                total: GET_TOTAL,
                pageSize: GET_PAGE_SIZE,
                numPages: GET_NUM_PAGES,
                products: GET_LIST,
            }),

            page: {
                get: function () {
                    return this.GET_PAGE_NUMBER;
                },

                set: function (page) {
                    this.loadPage(page);
                }
            },

            countSelected() {
                return Object.values(this.checkboxes).reduce((acc, val) => acc + val, 0);
            },

            checkAll() {
                let BreakException = {};

                try {
                    this.products.forEach(product => {
                        if (!this.massAll(this.massProductsType).includes(product.id))
                            throw BreakException;
                    });
                } catch (e) {
                    if (e !== BreakException) throw e;
                    return false;
                }
                return true;
            },

            editedShowColumns() {
                return this.columns.filter(function(column) {
                    return !column.isAlwaysShown;
                })
            }
        },

        created() {
            window.onpopstate = () => {
                let query = qs.parse(document.location.search.substr(1));
                if (query.page) {
                    this.page = query.page;
                }
            };

            this.opened = this.isHiddenFilterDefaultOpen();
        }
    }
</script>

<style scoped>
    .total-products{
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .switch-btn{
        min-width: 190px;
    }
</style>