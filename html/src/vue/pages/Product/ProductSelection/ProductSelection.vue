<template>
    <layout-main>
        <h1>Selection Products test</h1>
        <b-card>
            <div class="row">
                <f-multi-select v-model="filter.merchants" :options="toOptionsArray(options.merchants)" class="col-sm-12 col-md-8 col-xl-8">
                    Мерчант
                </f-multi-select>
                <bulk-offer-loader
                        :loaded-products="iSelectedProductIds"
                        show-report
                        :loader="offerLoader"
                        :return-mode="offerLoaderReturnModes.PRODUCT"
                        @load="onLoadOffers"
                />
            </div>
            <template v-slot:footer>
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего товаров: {{ total }}.</span>
            </template>
        </b-card>

        <table class="table">
            <thead>
            <tr>
                <th>offer id</th>
                <th>Мерчант</th>
                <th>Артикул</th>
                <th>product id</th>
                <th>Товар</th>
                <th>Бренд</th>
                <th>На витрине</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="product in products">
                <td>{{product.offerId}}</td>
                <td>{{product.merchantId}}</td>
                <td> ??? </td>
                <th>{{product.id}}</th>
                <td>{{product.name}}</td>
                <td>{{product.brandName}}</td>
                <td> {{product.active}} </td>
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
        name: "ProductSelection",

        components: {
            BulkOfferLoader,
            FMultiSelect
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

            return {
                checkboxes: {},
                filter,
                selectedProductIds: this.iSelectedProductIds,
                offerLoaderReturnModes: returnMode,
                opened: false,
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
        },

        created() {
            console.log('created --- ProductSelection');

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

</style>