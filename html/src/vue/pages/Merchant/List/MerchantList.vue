<template>
    <layout-main>
        <div class="mt-3 mb-3 shadow p-3">
            <button @click="openModal('merchantCreate')" class="btn btn-dark">Создать мерчанта</button>
        </div>
        <div class="mt-3 mb-3 shadow p-3">
            <div class="row">
                <f-input v-model="filter.name" class="col-lg-6 col-md-12">Компания</f-input>
                <f-multi-select
                        v-model="filter.status"
                        :options="statusOptions"
                        class="col-lg-6 col-md-12"
                >Статус</f-multi-select>
            </div>
            <button @click="applyFilter" class="btn btn-dark">Применить</button>
            <button @click="clearFilter" class="btn btn-secondary">Очистить</button>
        </div>
        <div class="mb-3">
            Всего: {{ pager.total }}. <span v-if="selectedMerchants.length">Выбрано: {{selectedMerchants.length}}</span>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th></th>
                <th>№</th>
                <th>Компания</th>
                <th>Заявитель</th>
                <th>Дата подачи</th>
                <th>Статус</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="merchant in merchants">
                <td>
                    <input type="checkbox" :checked="merchantSelected(merchant.id)"
                           @change="e => selectMerchant(e, merchant.id)">
                </td>
                <td>{{ merchant.id }}</td>
                <td><a :href="getRoute('merchant.detail', {id: merchant.id})">{{ merchant.display_name }}</a></td>
                <td>{{ merchant.user ? merchant.user.full_name : '' }}</td>
                <td>{{ merchant.created_at }}</td>
                <td><span class="badge" :class="statusClass(merchant.status)">{{ statusName(merchant.status) }}</span>
                </td>
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

        <merchant-create-modal @created="applyFilter"></merchant-create-modal>
    </layout-main>
</template>

<script>

    import Services from '../../../../scripts/services/services';
    import withQuery from 'with-query';

    import FMultiSelect from '../../../components/filter/f-multi-select.vue';
    import FInput from '../../../components/filter/f-input.vue';

    import MerchantCreateModal from "./components/merchant-create-modal.vue";

    import modalMixin from "../../../mixins/modal.js";

    const cleanFilter = {
        name: '',
        status: []
    };

    export default {
        name: 'page-index',
        mixins: [modalMixin],
        components: {
            FMultiSelect,
            FInput,

            MerchantCreateModal,
        },
        props: {
            done: {},
            iMerchants: {},
            iPager: {},
            iFilter: {},
            options: {}
        },
        data() {
            let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
            filter.status = filter.status.map(status => parseInt(status));
            return {
                merchants: this.iMerchants,
                pager: this.iPager,
                currentPage: this.iCurrentPage || 1,
                filter,
                selectedMerchants: []
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
                Services.net().get(this.route('merchant.listPage'), {
                    done: this.done,
                    page: this.currentPage,
                    filter: this.filter,
                    //sort: this.sort,
                }).then(data => {
                    this.merchants = data.items;
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
            statusName(id) {
                let status = this.options.statuses[id];
                return status ? status.name : 'N/A';
            },
            statusClass(id) {
                switch (id) {
                    case 1:
                        return 'badge-secondary';
                    case 2:
                        return 'badge-info';
                    case 3:
                        return 'badge-warning';
                    case 4:
                        return 'badge-danger';
                    case 5:
                        return 'badge-success';
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
            statusOptions() {
                return Object.values(this.options.statuses)
                    .map(status => ({value: status.id, text: status.name}))
                    .filter(status => status.value !== 6);
            }
        }
    };
</script>
