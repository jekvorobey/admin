<template>
    <layout-main>
        <div class="mt-3 mb-3 shadow p-3">
            <div class="row">
                <f-multi-select
                        v-model="filter.saleStatus"
                        :options="statusOptions"
                        class="col-lg-6 col-md-12"
                >Статус продажи</f-multi-select>
            </div>
            <button @click="applyFilter" class="btn btn-dark">Применить</button>
            <button @click="clearFilter" class="btn btn-secondary">Очистить</button>
        </div>
        <div class="mb-3">
            Всего предложений: {{ pager.total }}.
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Мерчант</th>
                    <th>Статус продажи</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="offer in offers">
                    <td>{{offer.productName}}</td>
                    <td>{{offer.merchantName}}</td>
                    <td>{{saleStatusName(offer.sale_status)}}</td>
                </tr>
            </tbody>
        </table>
        <div>
            <b-pagination
                    v-if="pager.pages !== 1"
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

    import Services from "../../../../scripts/services/services";
    import withQuery from "with-query";

    import FMultiSelect from "../../../components/filter/f-multi-select.vue";
    import FInput from "../../../components/filter/f-input.vue";
    import {mapGetters} from "vuex";

    const cleanFilter = {
        saleStatus: []
    };

    export default {
        components: {
            FMultiSelect,
            FInput,
        },
        props: {
            iOffers: {},
            iPager: {},
            iCurrentPage: {},
            iFilter: {},
            options: {}
        },
        data() {
            let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
            filter.saleStatus = filter.saleStatus.map(status => parseInt(status));
            return {
                offers: this.iOffers,
                pager: this.iPager,
                currentPage: this.iCurrentPage || 1,
                filter,
            };
        },
        methods: {
            changePage(newPage) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: newPage,
                    filter: this.filter,
                    //sort: this.sort
                }));
            },
            loadPage() {
                Services.net().get(this.route('offers.listPage'), {
                    page: this.currentPage,
                    filter: this.filter,
                    //sort: this.sort,
                }).then(data => {
                    this.offers = data.offers;
                    if (data.pager) {
                        this.pager = data.pager
                    }
                });
            },
            applyFilter() {
                this.changePage(1);
                this.loadPage();
            },
            clearFilter() {
                this.$set(this, 'filter', JSON.parse(JSON.stringify(cleanFilter)));
                this.applyFilter();
            },

            saleStatusName(statusId) {
                return this.options.saleStatus[statusId] ? this.options.saleStatus[statusId].name : 'N/A';
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
            ...mapGetters(['getRoute']),
            statusOptions () {
                return Object.values(this.options.saleStatus).map(status => ({text: status.name, value: status.id}));
            }
        }
    };
</script>
