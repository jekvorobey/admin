<template>
    <layout-main>
        <div class="mt-3 mb-3 shadow p-3">
            <div class="row">
                <f-date v-model="filter.created_at" class="col-lg-3 col-md-6" range>
                    Дата регистрации
                </f-date>
                <f-multi-select v-model="filter.rating" :options="ratingOptions" class="col-lg-3 col-md-6">
                    Рейтинг
                </f-multi-select>
                <f-multi-select v-model="filter.status" :options="statusOptions" class="col-lg-3 col-md-6">
                    Статус
                </f-multi-select>
                <f-input v-model="filter.id" class="col-lg-3 col-md-6">ID</f-input>
                <f-input v-model="filter.legal_name" class="col-lg-3 col-md-6">Название организации</f-input>
                <f-input v-model="filter.operator_first_name" class="col-lg-3 col-md-6">Имя</f-input>
                <f-input v-model="filter.operator_last_name" class="col-lg-3 col-md-6">Фамилия</f-input>
                <f-input v-model="filter.operator_middle_name" class="col-lg-3 col-md-6">Отчество</f-input>
                <f-input v-model="filter.operator_email" class="col-lg-3 col-md-6">Email</f-input>
                <f-input v-model="filter.operator_phone" class="col-lg-3 col-md-6">Телефон</f-input>
                <div class="form-group col-lg-3 col-md-6">
                    <label for="manager_id">Менеджер</label>
                    <div class="input-group input-group-sm">
                        <select class="form-control" v-model="filter.manager_id" id="manager_id">
                            <option :value="null">-</option>
                            <option v-for="(manager, id) in managers" :value="id">{{ manager }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <button @click="loadPage" class="btn btn-dark">Применить</button>
            <button @click="clearFilter" class="btn btn-secondary">Очистить</button>
        </div>
        <div class="mb-3">
            Всего: {{ pager.total }}. <span v-if="selectedMerchants.length">Выбрано: {{selectedMerchants.length}}</span>
        </div>

        <div class="btn-toolbar mb-3">
            <div class="input-group">
                <select class="custom-select" v-model="newStatus">
                    <option :value="null">Выбрать статус</option>
                    <option v-for="status in options.statuses" :value="status.id">{{ status.name }}</option>
                </select>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" :disabled="!selectedMerchants.length || !newStatus" @click="changeStatus">
                        <fa-icon icon="save"/>
                    </button>
                </div>
            </div>
            <b-button class="btn btn-success ml-2" v-b-modal="modalIdCreateMerchant">Создать мерчанта</b-button>

        </div>

        <table class="table">
            <thead>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Дата регистрации</th>
                <th>Название организации</th>
                <th>ФИО контактного лица</th>
                <th>Email</th>
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
                <td>{{ merchant.manager_id ? managers[merchant.manager_id] : '' }}</td>
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

        <modal-create-merchant :id="modalIdCreateMerchant" :communication-methods="options.communicationMethods"/>
    </layout-main>
</template>

<script>

    import Services from '../../../../scripts/services/services';
    import withQuery from 'with-query';

    import FMultiSelect from '../../../components/filter/f-multi-select.vue';
    import FInput from '../../../components/filter/f-input.vue';

    import FDate from '../../../components/filter/f-date.vue';

    import ModalCreateMerchant from "./components/modal-create-merchant.vue";

    const cleanFilter = {
    id: '',
    legal_name: '',
    operator_first_name: '',
    operator_last_name: '',
    operator_middle_name: '',
    operator_email: '',
    operator_phone: '',
    manager_id: '',
    status: [],
    rating: [],
    created_at: []
};

export default {
    name: 'page-index',
    components: {FDate, FMultiSelect, FInput, ModalCreateMerchant},
    props: [
        'done',
        'iMerchants',
        'iPager',
        'iFilter',
        'iCurrentPage',
        'options',
        'managers'
    ],
    data() {
        let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
        filter.status = filter.status.map(status => parseInt(status));
        filter.rating = filter.rating.map(rating => parseInt(rating));
        return {
            modalIdCreateMerchant: 'modalIdCreateMerchant',
            merchants: this.iMerchants,
            pager: this.iPager,
            currentPage: this.iCurrentPage || 1,
            filter,
            selectedMerchants: [],
            newStatus: null,
        };
    },
    methods: {
        pushRoute() {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: this.currentPage,
                filter: this.filter,
            }));
        },
        loadPage() {
            Services.showLoader();
            Services.net().get(this.route('merchant.listPage'), {
                done: this.done ? 1 : 0,
                page: this.currentPage,
                filter: this.filter,
            }).then(data => {
                this.merchants = data.items;
                if (data.pager) {
                    this.pager = data.pager
                }
                this.selectedMerchants = [];
                this.pushRoute(this.currentPage);
            }).finally(() => {
                Services.hideLoader();
            });
        },
        changeStatus() {
            Services.showLoader();
            Services.net().put(this.route('merchant.listPage.changeStatus'), {
                status: this.newStatus,
                ids: this.selectedMerchants,
            }).catch(() => {
                Services.hideLoader();
            }).then(data => {
                this.newStatus = null;
                this.loadPage();
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
        },
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
