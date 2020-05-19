<template>
    <layout-main>
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
                    <f-input v-model="filter.id" class="col">
                        ID заявки
                    </f-input>
                    <f-select
                            v-model="filter.type"
                            :options="toOptionsArray(options.types)"
                            class="col-2">
                        Тип заявки
                    </f-select>
                    <f-select v-model="filter.status"
                              :options="toOptionsArray(options.statuses)"
                              class="col-2">
                        Статус заявки
                    </f-select>
                </div>
                <transition name="slide">
                    <div v-if="opened">
                        <div class="additional-filter pt-3 mt-3">
                            <div class="row">
                                <f-select
                                        v-model="filter.merchant"
                                        :options="toOptionsArray(options.merchants)"
                                        class="col">
                                    Мерчант
                                </f-select>
                                <f-select
                                        v-model="filter.unpack"
                                        :options="toOptionsArray(options.unpack)"
                                        class="col">
                                    Тип съёмки
                                </f-select>
                                <f-input v-model="filter.user" class="col">
                                    Автор
                                </f-input>
                                <f-date range v-model="filter.created_at" class="col">
                                    Дата создания
                                </f-date>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 mt-3">
                <a :href="getRoute('contentClaims.create')" class="btn btn-success">Создать заявку</a>
                <button class="btn btn-warning" :disabled="countSelected < 1" @click="changeClaimStatus()">Изменить статус
                    <template v-if="countSelected <= 1">заявки</template>
                    <template v-else>заявок</template>
                </button>
                <button class="btn btn-danger" :disabled="countSelected < 1" @click="deleteClaim()">Удалить
                    <template v-if="countSelected <= 1">заявку</template>
                    <template v-else>заявки</template>
                </button>
                <button class="btn btn-secondary">Скачать документы</button>
            </div>
        </div>

        <table class="table" v-if="claims && claims.length > 0">
            <thead>
                <tr>
                    <th><input type="checkbox" v-model="selectAll" @click="changeSelectAll()"></th>
                    <th>ID</th>
                    <th>Тип</th>
                    <th>Статус</th>
                    <th>Мерчант</th>
                    <th>Кол-во товаров</th>
                    <th>Тип съёмки</th>
                    <th>Автор</th>
                    <th>Создана</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="claim in claims">
                    <td><input type="checkbox" v-model="checkboxes[claim.id]"></td>
                    <td><a :href="getRoute('contentClaims.detail', {id: claim.id})">{{ claim.id }}</a></td>
                    <td>{{ typeName(claim.type) }}</td>
                    <td><span class="badge" :class="statusClass(claim.status)">{{ statusName(claim.status) }}</span></td>
                    <td>{{ claim.merchantName }}</td>
                    <td>{{ claim.product_ids.length }}</td>
                    <td>{{ unpackName(claim.unpacking) }}</td>
                    <td>{{ claim.userName }}</td>
                    <td>{{ claim.created_at }}</td>
                </tr>
            </tbody>
        </table>
        <p v-else class="text-center p-3">Ничего не найдено!</p>
        <b-pagination
                v-if="pager.pages > 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                @change="changePage"
                :hide-goto-end-buttons="pager.pages < 10"
                class="float-right"
        ></b-pagination>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('ChangeClaimStatus')">
                <div slot="header">
                    <b>Обновление статуса заявок</b>
                </div>
                <div slot="body">
                    <claim-list :claims="selectedClaims"></claim-list>
                    <v-select v-model="newStatus" :options="toOptionsArray(availableStatusOptions)" class="mt-3">Выберите новый статус</v-select>
                    <button class="btn btn-warning mt-3" type="button" @click="approveChangeStatus()" :disabled="processing">Изменить статус</button>
                </div>
            </modal>
        </transition>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('DeleteClaim')">
                <div slot="header">
                    <b>Вы уверены, что хотите удалить следующие заявки?</b>
                </div>
                <div slot="body">
                    <claim-list :claims="selectedClaims"></claim-list>
                    <button class="btn btn-danger mt-3" type="button" @click="approveDelete()" :disabled="processing">Удалить</button>
                </div>
            </modal>
        </transition>

        <button class="" type="button" @click="approveDelete()" :disabled="processing">Удалить</button>

    </layout-main>
</template>

<script>

    import withQuery from 'with-query';
    import qs from 'qs';
    import Services from '../../../../../scripts/services/services';
    import FInput from '../../../../components/filter/f-input.vue';
    import FCheckbox from '../../../../components/filter/f-checkbox.vue';
    import FDate from '../../../../components/filter/f-date.vue';
    import FSelect from '../../../../components/filter/f-select.vue';
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../../mixins/modal';
    import claimList from '../components/claim-list.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';

    const cleanHiddenFilter = {
        merchant: '',
        unpack: null,
        user: '',
        created_at: '',
    };

    const cleanFilter = Object.assign({
        id: '',
        type: null,
        status: null
    }, cleanHiddenFilter);

export default {
    components: {
        FInput,
        FCheckbox,
        FDate,
        FSelect,
        modal,
        claimList,
        VSelect
    },
    mixins: [modalMixin],
    props: {
        iClaims: Array,
        options: {},
        iPager: {},
        iCurrentPage: Number,
        iFilter: {}
    },
    data() {
        return {
            currentPage: this.iCurrentPage,
            claims: this.iClaims,
            pager: this.iPager,
            filter: {},
            appliedFilter: {},
            opened: false,
            checkboxes: {},
            selectAll: false,
            processing: false,
            newStatus: 0,
        };
    },
    methods: {
        statusName(statusId) {
            return this.options.statuses[statusId] || 'N/A';
        },
        typeName(typeId) {
            return this.options.types[typeId] || 'N/A';
        },
        unpackName(unpackId) {
            return this.options.unpack[unpackId] || '—';
        },
        changePage(newPage) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: newPage,
                filter: this.appliedFilter,
                //sort: this.sort
            }));
        },
        loadPage() {
            Services.showLoader();
            Services.net().get(this.route('contentClaims.pagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
                //sort: this.sort,
            }).then(data => {
                this.claims = data.items;
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
                if (value && Object.keys(cleanFilter).indexOf(key) !== -1) {
                    tmpFilter[key] = value;
                }
            }
            this.appliedFilter = tmpFilter;
            console.log(JSON.stringify(tmpFilter));
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
            // for (let entry of Object.entries(cleanFilter)) {
            //     this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
            // }
            // this.applyFilter();
            // console.log(this.statusOptions);
            console.log(this.availableStatusOptions);
            console.log(this.options.statuses);

        },
        toOptionsArray(options) {
            let a = [];
            for (let [k, v] of Object.entries(options)) {
                a.push({value: parseInt(k), text: v});
            }
            return a;
        },
        changeClaimStatus() {
            this.openModal('ChangeClaimStatus');
        },
        deleteClaim() {
            this.openModal('DeleteClaim');
        },
        changeSelectAll() {
            let selected = !this.selectAll;
            let checkboxes = {};
            for (let claim of this.claims) {
                checkboxes[claim.id] = selected;
            }
            this.checkboxes = checkboxes;
        },
        approveChangeStatus() {
            if (!this.newStatus) {
                return;
            }

            this.processing = true;
            Services.net().put(this.route('contentClaims.changeStatuses'), {
                claim_ids: this.selectedIds,
                status: this.newStatus,
            }).then(data => {
                this.processing = false;
                this.closeModal('ChangeClaimStatus');
                let message = this.countSelected > 1 ? "Статус заявок изменён!" : "Статус заявки изменён!";
                Services.msg(message);
                window.location.reload();
            });
        },
        approveDelete() {
            this.processing = true;
            Services.net().delete(this.route('contentClaims.deleteClaims'), {
                claim_ids: this.selectedIds,
            }).then(data => {
                this.processing = false;
                this.closeModal('DeleteClaim');
                let message = this.countSelected > 1 ? "Заявки удалены!" : "Заявка удалена!";
                Services.msg(message);
                window.location.reload();
            });
        },
        statusClass(statusId) {
            switch (statusId) {
                case 1: return 'badge-info';
                case 2: return 'badge-primary';
                case 3: return 'badge-success';
                case 4: return 'badge-warning';
                case 5: return 'badge-light';
                case 6: return 'badge-secondary';
                case 7: return 'badge-danger';
                // default: return 'badge-light';
            }
        },
    },
    computed: {
        countSelected() {
            return Object.values(this.checkboxes).reduce((acc, val) => acc + val, 0);
        },
        selectedClaims() {
            return this.iClaims.filter(claim => {
                return (claim.id in this.checkboxes) && this.checkboxes[claim.id];
            });
        },
        selectedIds() {
            return this.iClaims.filter(claim => {
                return (claim.id in this.checkboxes) && this.checkboxes[claim.id];
            }).map(claim => claim.id);
        },
        availableStatusOptions() {
            // return [];
            let noDeliveryClaim = this.selectedClaims.some(claim => {
                return this.options.noUnpack.includes(claim.type);
            });
            // if (!noDeliveryClaim) return this.options.adjustStatuses;
            if (!noDeliveryClaim) {
                console.log('no');
                return this.options.adjustStatuses;
            }
            console.log('yes');

            return this.options.statuses.filter(status => {
                console.log(status);
                return (!this.options.deliveryConfirm.includes(status));
            });
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
    }
};
</script>
