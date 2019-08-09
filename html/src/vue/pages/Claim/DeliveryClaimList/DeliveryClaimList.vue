<template>
    <layout-main>
        <h1>Заявки доставу товара</h1>
        <div class="d-flex flex-row justify-content-between">
            <div>
                <div class="custom-control custom-checkbox mt-3">
                    <input v-model="filter.showDone" @change="applyFilter"
                            true-value="true"
                            false-value="false"
                            type="checkbox"
                            class="custom-control-input"
                            id="customCheck1">
                    <label class="custom-control-label" for="customCheck1">Показывать готовые заявки</label>
                </div>
            </div>
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
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Статус</th>
                    <th>Кол-во товаров</th>
                    <th>Мерчант</th>
                    <th>Создана</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="claim in claims">
                    <td><a :href="getRoute('deliveryClaims.detail', {id: claim.id})">{{ claim.id }}</a></td>
                    <td>{{ statusName(claim.status) }}</td>
                    <td>{{ claim.payload.productIds.length }}</td>
                    <td>{{ claim.merchant }}</td>
                    <td>{{ claim.created_at }}</td>
                </tr>
            </tbody>
        </table>
    </layout-main>
</template>

<script>

import withQuery from 'with-query';
import qs from 'qs';
import Services from "../../../../scripts/services/services";
import {mapGetters} from "vuex";

export default {
    name: 'page-buffer',
    props: {
        iClaims: Array,
        statuses: {},
        iPager: {},
        iCurrentPage: Number,
        iFilter: {}
    },
    data() {
        return {
            currentPage: this.iCurrentPage,
            claims: this.iClaims,
            pager: this.iPager,
            filter: Object.assign({
                showDone: false
            }, this.iFilter),
        };
    },
    methods: {
        statusName(statusId) {
            return this.statuses[statusId] || 'N/A';
        },
        changePage(newPage) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: newPage,
                filter: this.filter,
                //sort: this.sort
            }));
        },
        loadPage() {
            Services.net().get(this.route('deliveryClaims.pagination'), {
                page: this.currentPage,
                filter: this.filter,
                //sort: this.sort,
            }).then(data => {
                this.claims = data.items;
                if (data.pager) {
                    this.pager = data.pager
                }
            });
        },
        applyFilter() {
            this.changePage(1);
            this.loadPage();
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
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
    }
};
</script>
