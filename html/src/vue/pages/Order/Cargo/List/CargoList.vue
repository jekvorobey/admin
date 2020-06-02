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
                    <f-input v-model="filter.id" class="col-2">
                        ID
                    </f-input>
                    <f-multi-select v-model="filter.merchant_id" :options="merchantOptions" class="col">
                        Мерчант
                    </f-multi-select>
                    <f-multi-select v-model="filter.status" :options="statusOptions" class="col">
                        Статус груза
                    </f-multi-select>
                    <f-multi-select v-model="filter.delivery_service" :options="deliveryServiceOptions" class="col">
                        Служба доставки
                    </f-multi-select>
                    <f-multi-select v-model="filter.store_id" :options="storeOptions" class="col">
                        Склад отгрузки
                    </f-multi-select>
                </div>

                <transition name="slide">
                    <div v-if="opened" class="additional-filter pt-3 mt-3">
                        <div class="row">
                            <f-input v-model="filter.shipment_number" class="col-2">
                                № заказа в грузе
                            </f-input>
                            <f-date v-model="filter.created_at" class="col-2" range confirm>
                                Дата создания
                            </f-date>
                            <f-select v-model="filter.is_canceled" :options="booleanOptions" class="col-2">
                                Отменен
                            </f-select>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <div class="action-bar d-flex justify-content-start">
                <dropdown :items="dropdownItems" @select="downloadDoc" class="mr-4 cargo-btn">
                    <fa-icon icon="file-download"></fa-icon>
                    Скачать документы
                </dropdown>
                <dropdown :items="dropdownItems" @select="printDoc" class="mr-4 cargo-btn">
                    <fa-icon icon="print"></fa-icon>
                    Распечатать документы
                </dropdown>
            </div>
        </div>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all-page-cargos" v-model="isSelectAllPageCargos" @click="selectAllPageCargos()">
                        <label for="select-all-page-cargos" class="mb-0">Все</label>
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
                <tr v-for="cargo in cargos">
                    <td><input type="checkbox" value="true" class="cargo-select" :value="cargo.id"></td>
                    <td v-for="column in columns" v-if="column.isShown">
                        <template v-if="column.code === 'status'">
                            <cargo-status :status='cargo.status'/>
                            <template v-if="order.is_canceled">
                                <br><span class="badge badge-danger">Отменен</span>
                            </template>
                        </template>
                        <div v-else v-html="column.value(cargo)"></div>
                    </td>
                    <td v-for="column in columns" v-if="column.isShown" v-html="column.value(cargo)"></td>
                    <td></td>
                </tr>
                <tr v-if="!cargos.length">
                    <td :colspan="columns.length + 1">Грузов нет</td>
                </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="pager.pages > 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                @change="changePage"
                :hide-goto-end-buttons="pager.pages < 10"
                class="float-right"
        ></b-pagination>
    </layout-main>
</template>

<script>
    import Service from '../../../../../scripts/services/services';
    import withQuery from 'with-query';
    import qs from 'qs';
    import FInput from '../../../../components/filter/f-input.vue';
    import FDate from '../../../../components/filter/f-date.vue';
    import FSelect from '../../../../components/filter/f-select.vue';
    import FMultiSelect from '../../../../components/filter/f-multi-select.vue';
    import Dropdown from '../../../../components/dropdown/dropdown.vue';
    import ModalColumns from '../../../../components/modal-columns/modal-columns.vue';
    import Helpers from '../../../../../scripts/helpers';
    import modalMixin from '../../../../mixins/modal';

    const cleanHiddenFilter = {
    shipment_number: '',
    created_at: [],
    is_canceled: '',
};

const cleanFilter = Object.assign({
    id: '',
    merchant_id: [],
    status: [],
    delivery_service: [],
    store_id: [],
}, cleanHiddenFilter);

const serverKeys = [
    'id',
    'merchant_id',
    'status',
    'delivery_service',
    'store_id',
    'shipment_number',
    'created_at',
    'is_canceled',
];

export default {
    props: [
        'iCargos',
        'iCurrentPage',
        'iPager',
        'stores',
        'iFilter',
        'iSort',
        'merchants',
    ],
    components: {
        FInput,
        FDate,
        FSelect,
        FMultiSelect,
        ModalColumns,
        Dropdown,
    },
    mixins: [modalMixin],
    data() {
        let self = this;
        let filter = Object.assign({}, cleanFilter, this.iFilter);
        filter.merchant_id = filter.merchant_id.map(value => parseInt(value));
        filter.delivery_service = filter.delivery_service.map(value => parseInt(value));
        filter.status = filter.status.map(value => parseInt(value));
        filter.store_id = filter.store_id.map(value => parseInt(value));

        return {
            opened: false,
            currentPage: this.iCurrentPage,
            cargos: this.iCargos,
            filter,
            sort: this.iSort,
            appliedFilter: {},
            pager: this.iPager,
            isSelectAllPageCargos: false,
            columns: [
                {
                    name: 'ID',
                    code: 'id',
                    value: function(cargo) {
                        return '<a href="' + self.getRoute('cargo.detail', {id: cargo.id}) + '">' +
                            cargo.id + '</a>';
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Мерчант',
                    code: 'merchant',
                    value: function(cargo) {
                        return cargo.merchant.legal_name;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Дата создания',
                    code: 'date',
                    value: function(cargo) {
                        return cargo.created_at;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Служба доставки',
                    code: 'delivery_service',
                    value: function(cargo) {
                        return cargo.delivery_service.name;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Склад',
                    code: 'store',
                    value: function(cargo) {
                        return cargo.store.name;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Статус',
                    code: 'status',
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Кол-во коробок',
                    code: 'package_qty',
                    value: function(cargo) {
                        return cargo.package_qty;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Комментарий',
                    code: 'comment',
                    value: function(cargo) {
                        return cargo.shipping_problem_comment;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
            ],
            dropdownItems: [
                {value: 1, text: 'Курьеру'},
            ],
        };
    },
    methods: {
        changePage(newPage) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: newPage,
                filter: this.appliedFilter,
                sort: this.sort
            }));
        },
        loadPage() {
            Service.net().get(this.route('cargo.pagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
                sort: this.sort,
            }).then(data => {
                this.cargos = data.cargos;
                if (data.pager) {
                    this.pager = data.pager
                }
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
            this.changePage(1);
            this.loadPage();
        },
        clearFilter() {
            for (let entry of Object.entries(cleanFilter)) {
                this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
            }
            this.applyFilter();
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
        isHiddenFilterDefaultOpen() {
            for (let entry of Object.entries(cleanHiddenFilter)) {
                if (!Helpers.isEqual(entry[1], this.filter[entry[0]]) && entry[1] !== this.filter[entry[0]]) {
                    return true;
                }
            }
            return false;
        },
        selectAllPageCargos() {
            let checkboxes = document.getElementsByClassName('cargo-select');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = this.isSelectAllPageCargos ? '' : 'checked';
            }
        },
        showChangeColumns() {
            this.openModal('list_columns');
        },
        downloadDoc(id) {
            window.open('/manual/docs.pdf'); //todo
        },
        printDoc(id) {
            let iframe = this._printIframe;
            if (!this._printIframe) {
                iframe = this._printIframe = document.createElement('iframe');
                document.body.appendChild(iframe);

                iframe.style.display = 'none';
                iframe.onload = function() {
                    setTimeout(function() {
                        iframe.focus();
                        iframe.contentWindow.print();
                    }, 1);
                };
            }

            iframe.src = '/manual/docs.pdf';
        }
    },
    computed: {
        merchantOptions() {
            return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.legal_name}));
        },
        statusOptions() {
            return Object.values(this.cargoStatuses).map(status => ({
                value: status.id,
                text: status.name
            }));
        },
        storeOptions() {
            return Object.values(this.stores).map(store => ({
                value: store.id,
                text: store.name + ' (' + store.merchant.legal_name + ')'
            }));
        },
        deliveryServiceOptions() {
            return Object.values(this.deliveryServices).map(deliveryService => ({value: deliveryService.id, text: deliveryService.name}));
        },
        booleanOptions() {
            return [{value: 0, text: 'Нет'}, {value: 1, text: 'Да'}];
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
                this.currentPage = query.page;
            }
        };
        this.opened = this.isHiddenFilterDefaultOpen();
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
    .action-bar {
        padding: 10px 20px;
    }
    .cargo-btn {
        cursor: pointer;
    }
</style>