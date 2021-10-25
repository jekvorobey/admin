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
                <f-input v-model="filter.operator_full_name" class="col-lg-3 col-md-6">ФИО</f-input>
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
            Всего: {{ pager.total }}. <span v-if="selectedMerchantIds.length">Выбрано: {{selectedMerchantIds.length}}</span>
        </div>

        <div class="btn-toolbar mb-3" v-if="canUpdate(blocks.merchants)">
            <div class="input-group">
                <select class="custom-select" v-model="newStatus">
                    <option :value="null">Выбрать статус</option>
                    <option v-for="status in options.statuses" :value="status.id">{{ status.name }}</option>
                </select>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" :disabled="!selectedMerchantIds.length || !newStatus" @click="changeStatus">
                        <fa-icon icon="save"/>
                    </button>
                </div>
            </div>
            <b-button class="btn btn-success ml-2" v-b-modal="modalIdCreateMerchant">Создать мерчанта</b-button>
            <button class="btn btn-outline-primary ml-2" :disabled="!selectedMerchantIds.length" @click="onShowModalCreate()">Отправить сообщение</button>
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
                            @change="e => selectMerchant(e, merchant)">
                </td>
                <td>{{ merchant.id }}</td>
                <td>{{ merchant.created_at }}</td>
                <td v-if="canUpdate(blocks.merchants)"><a :href="getRoute('merchant.detail', {id: merchant.id})">{{ merchant.legal_name }}</a></td>
                <td v-else>{{ merchant.legal_name }}</td>
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

        <b-modal id="modal-broadcast-create" title="Создание чата" hide-footer>
            <communication-chat-creator v-if="this.selectedOperators.length"
                                        :usersProp="selectedOperators.map(operator => {return {'id': operator.id, 'email': operator.email}})"
                                        :userSendIds="selectedOperators.map(operator => operator.id)"
            />
            <span v-else>Операторы отсутствуют</span>
        </b-modal>
    </layout-main>
</template>

<script>

    import Services from '../../../../scripts/services/services';
    import withQuery from 'with-query';

    import FMultiSelect from '../../../components/filter/f-multi-select.vue';
    import FInput from '../../../components/filter/f-input.vue';

    import FDate from '../../../components/filter/f-date.vue';

    import ModalCreateMerchant from "./components/modal-create-merchant.vue";
    import CommunicationChatCreator
        from "../../../components/communication/communication-chat-creator/communication-chat-creator.vue";

    const cleanFilter = {
    id: '',
    legal_name: '',
    operator_full_name: '',
    operator_email: '',
    operator_phone: '',
    manager_id: '',
    status: [],
    rating: [],
    created_at: []
};

export default {
    name: 'page-index',
    components: {FDate, FMultiSelect, FInput, ModalCreateMerchant, CommunicationChatCreator},
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
            selectedMerchantIds: [],
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
                this.selectedMerchantIds = [];
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
                ids: this.selectedMerchantIds,
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
            return this.selectedMerchantIds.indexOf(id) !== -1;
        },
        selectMerchant(e, merchant) {
            if (e.target.checked) {
                this.selectedMerchantIds.push(merchant.id);
                this.selectedMerchants.push(merchant);
            } else {
                let index = this.selectedMerchantIds.indexOf(merchant.id);
                if (index !== -1) {
                    this.selectedMerchantIds.splice(index, 1);
                    this.selectedMerchants.splice(index, 1);
                }
            }
        },
        onShowModalCreate() {
            this.$bvModal.show('modal-broadcast-create');
        },
        onCloseModalCreate() {
            this.$bvModal.hide('modal-broadcast-create');
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
        selectedOperators() {
            let operators = [];
            if (this.selectedMerchants.length) {
                this.selectedMerchants.forEach(function (merchant) {
                    operators.push(...Object.values(merchant.users));
                });
            }
            return operators;
        },
    },
    created() {
        Services.event().$on('closeModalCreate', this.onCloseModalCreate);
    }
};
</script>
