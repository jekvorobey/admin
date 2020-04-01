<template>
    <layout-main>
        <div class="mt-3 mb-3 shadow p-3">
            <div class="row">
                <f-date v-model="filter.created_at" class="col-lg-3 col-md-6" range>
                    Дата регистрации
                </f-date>
                <f-multi-select v-model="filter.status" :options="statusOptions" class="col-lg-3 col-md-6">
                    Статус
                </f-multi-select>
                <f-multi-select v-model="filter.rating" :options="ratingOptions" class="col-lg-3 col-md-6">
                    Рейтинг
                </f-multi-select>
                <f-input v-model="filter.name" class="col-lg-3 col-md-6">Компания</f-input>
            </div>
            <button @click="loadPage" class="btn btn-dark">Применить</button>
            <button @click="clearFilter" class="btn btn-secondary">Очистить</button>
        </div>
        <div class="mb-3">
            Всего: {{ pager.total }}. <span v-if="selectedMerchants.length">Выбрано: {{selectedMerchants.length}}</span>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Дата регистрации</th>
                <th>Название организации</th>
                <th>ФИО контактного лица</th>
                <th>email</th>
                <th>Телефона</th>
                <th>Рейтинг</th>
                <th>Статус</th>
                <th>Менеджер</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="merchant in merchants">
                <td>
                    <input type="checkbox" :checked="merchantSelected(merchant.id)"
                            @change="e => selectMerchant(e, merchant.id)">
                </td>
                <td>{{ merchant.id }}</td>
                <td>{{ merchant.created_at }}</td>
                <td><a :href="getRoute('merchant.detail', {id: merchant.id})">{{ merchant.legal_name }}</a></td>
                <td>{{ merchant.user ? merchant.user.full_name : '' }}</td>
                <td>{{ merchant.user ? merchant.user.email : '' }}</td>
                <td>{{ merchant.user ? merchant.user.phone : '' }}</td>
                <td>{{ merchant.rating ? merchant.rating.name : '-'}}</td>
                <td><span class="badge" :class="statusClass(merchant.status)">{{ statusName(merchant.status) }}</span></td>
                <td>Ответственный менеджер</td>
            </tr>
            </tbody>
        </table>
        <div>
            <b-pagination
                    v-if="pager.pages !== 1"
                    v-model="currentPage"
                    :total-rows="pager.total"
                    :per-page="pager.pageSize"
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

import FDate from '../../../components/filter/f-date.vue';

const cleanFilter = {name: '', status: [], rating: [], created_at: [],};

export default {
    name: 'page-index',
    components: {FDate, FMultiSelect, FInput},
    props: ['done', 'iMerchants', 'iPager', 'iFilter', 'iCurrentPage', 'options',],
    data() {
        let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
        filter.status = filter.status.map(status => parseInt(status));
        filter.rating = filter.rating.map(rating => parseInt(rating));
        return {
            merchants: this.iMerchants,
            pager: this.iPager,
            currentPage: this.iCurrentPage || 1,
            filter,
            selectedMerchants: []
        };
    },
    methods: {
        pushRoute() {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: this.currentPage,
                filter: this.filter,
                //sort: this.sort
            }));
        },
        loadPage() {
            Services.showLoader();
            Services.net().get(this.route('merchant.listPage'), {
                done: this.done ? 1 : 0,
                page: this.currentPage,
                filter: this.filter,
                //sort: this.sort,
            }).then(data => {
                this.merchants = data.items;
                if (data.pager) {
                    this.pager = data.pager
                }
                this.pushRoute(this.currentPage);
            }).finally(() => {
                Services.hideLoader();
            });
        },
        clearFilter() {
            this.$set(this, 'filter', JSON.parse(JSON.stringify(cleanFilter)));
            this.loadPage();
        },
        statusName(id) {
            let status = this.options.statuses[id];
            return status ? status.name : 'N/A';
        },
        statusClass(id) {
            switch (id) {
                case this.merchantStatuses.created:
                    return 'badge-secondary';
                case this.merchantStatuses.review:
                    return 'badge-info';
                case this.merchantStatuses.cancel:
                    return 'badge-danger';
                case this.merchantStatuses.terms:
                    return 'badge-warning';
                case this.merchantStatuses.activation:
                    return 'badge-outline-success';
                case this.merchantStatuses.work:
                    return 'badge-success';
                case this.merchantStatuses.stop:
                    return 'badge-warning';
                case this.merchantStatuses.close:
                    return 'badge-danger';
            }
        },
        merchantSelected(id) {
            return this.selectedMerchants.indexOf(id) !== -1;
        },
        selectMerchant(e, id) {
            if (e.target.checked) {
                this.selectedMerchants.push(id);
            } else {
                let index = this.selectedMerchants.indexOf(id);
                if (index !== -1) {
                    this.selectedMerchants.splice(index, 1);
                }
            }
        }
    },
    watch: {
        currentPage() {
            this.loadPage();
        }
    },
    computed: {
        statusOptions() {
            return Object.values(this.options.statuses).map(status => ({value: status.id, text: status.name}));
        },
        ratingOptions() {
            return Object.values(this.options.ratings).map(rating => ({value: rating.id, text: rating.name}));
        },
    },
};
</script>
