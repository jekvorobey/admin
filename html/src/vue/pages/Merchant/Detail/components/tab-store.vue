<template>
    <div>
        <div class="card">
            <div class="card-header">
                Фильтр
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.id"  class="col-4">
                        ID
                    </f-input>
                    <f-input v-model="filter.name" class="col-8">
                        Название
                    </f-input>
                </div>
                <div class="row">
                    <f-input v-model="filter.address_string" class="col-12">
                        Адрес склада
                    </f-input>
                </div>
                <div class="row">
                    <f-input v-model="filter.contact_name" class="col-6">
                        ФИО контактного лица
                    </f-input>
                    <f-input v-model="filter.contact_phone" class="col-6">
                        Телефон контактного лица
                    </f-input>
                </div>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 mt-3">
                <a :href="getRoute('merchantStore.add')"
                   class="btn btn-success"
                >Создать склад</a>

                <button class="btn btn-secondary" disabled v-if="countSelected !== 1">Редактировать склад</button>
                <a :href="getRoute('merchantStore.edit', {id: selectedStores[0].id})" class="btn btn-warning" v-else>Редактировать склад</a>

                <button class="btn btn-danger" :disabled="countSelected < 1" @click="deleteStores()">
                    Удалить {{ pluralForm(countSelected, formsGenitive) }}
                </button>
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           id="select-all-page-stores"
                           v-model="selectAll"
                           @click="changeSelectAll()"
                    >
                    <label for="select-all-page-stores" class="mb-0">Все</label>
                </th>
                <th v-for="column in columns" v-if="column.isShown">{{ column.name }}</th>
                <th>
                    <button class="btn btn-light float-right" @click="showChangeColumns">
                        <fa-icon icon="cog"></fa-icon>
                    </button>
                    <modal-columns :i-columns="editedShowColumns"></modal-columns>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="store in stores">
                <td>
                    <input type="checkbox"
                           value="true"
                           v-model="checkboxes[store.id]"
                           :value="store.id">
                </td>
                <td v-for="column in columns" v-if="column.isShown" v-html="column.value(store)"></td>
            </tr>
            <tr v-if="!stores.length">
                <td :colspan="columns.length + 1">Склады отсутствуют</td>
            </tr>
            </tbody>
        </table>
        <b-pagination
                v-if="pager.pages > 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                :hide-goto-end-buttons="pager.pages < 10"
                class="float-right"
        ></b-pagination>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('DeleteStore')">
                <div slot="header">
                    <b>Вы уверены, что хотите удалить {{ pluralForm(countSelected, formsNextGenitive) }}
                        {{ pluralForm(countSelected, formsGenitive) }}?</b>
                </div>
                <div slot="body">
                    <div v-for="store in selectedStores">#{{ store.id }} {{ store.name }}</div>
                    <button class="btn btn-danger mt-3" type="button" @click="approveDelete()">Удалить</button>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import FInput from '../../../../components/filter/f-input.vue';

    import ModalColumns from "../../../../components/modal-columns/modal-columns.vue";
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from "../../../../mixins/modal.js";

    import Services from '../../../../../scripts/services/services.js';
    import Helpers from "../../../../../scripts/helpers.js";

    const cleanFilter = {
        id: null,
        name: '',
        address_string: '',
        contact_name: '',
        contact_phone: '',
    };

    const serverKeys = [
        'id',
        'name',
        'address_string',
        'contact_name',
        'contact_phone',
    ];

    const formsGenitiveConst  = [
        "склад",
        "склады",
        "склады",
    ];

    const formsNextGenitiveConst  = [
        "следующего",
        "следующих",
        "следующих",
    ];

    const formsPrepositionalConst  = [
        "складе",
        "складах",
        "складах",
    ];

    export default {
        name: 'tab-store',
        props: ['id'],
        components: {
            FInput,
            modal,
            ModalColumns,
        },
        mixins: [modalMixin],
        data() {
            // let self = this;
            let filter = Object.assign({}, cleanFilter);

            return {
                stores: [],
                filter,
                appliedFilter: {},
                selectAll: false,
                checkboxes: {},
                formsGenitive: formsGenitiveConst,
                formsNextGenitive: formsNextGenitiveConst,
                formsPrepositional: formsPrepositionalConst,
                currentPage: 1,
                pager: {},
                columns: [
                    {
                        name: '#',
                        code: 'id',
                        value: function(store) {
                            return store.id;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Название склада',
                        code: 'name',
                        value: function(store) {
                            return store.name;
                        },
                        isShown: true,
                        isAlwaysShown: true,
                    },
                    {
                        name: 'Адрес склада',
                        code: 'address',
                        value: function(store) {
                            return store.address;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'ФИО контактного лица',
                        code: 'contact_name',
                        value: function(store) {
                            return store.contact_name;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                    {
                        name: 'Телефон контактного лица',
                        code: 'contact_phone',
                        value: function(store) {
                            return store.contact_phone;
                        },
                        isShown: true,
                        isAlwaysShown: false,
                    },
                ],
            }
        },
        created() {
            this.loadPage();
        },
        methods: {
            showChangeColumns() {
                this.openModal('list_columns');
            },
            applyFilter() {
                let tmpFilter = {};
                for (let [key, value] of Object.entries(this.filter)) {
                    if (value && serverKeys.indexOf(key) !== -1) {
                        tmpFilter[key] = value;
                    }
                }
                this.appliedFilter = tmpFilter;
                this.loadPage();
            },
            clearFilter() {
                for (let entry of Object.entries(cleanFilter)) {
                    this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                }
                this.applyFilter();
            },
            loadPage() {
                Services.showLoader();
                Services.net().get(
                    this.getRoute('merchant.detail.store.pagination', {id: this.id}),
                    {
                        page: this.currentPage,
                        filter: this.appliedFilter,
                    }
                ).then(data => {
                    this.stores = data.stores;
                    if (data.pager) {
                        this.pager = data.pager
                    }
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            forEachStore(callback) {
                for (let i in this.stores) {
                    callback(this.stores[i]['id']);
                }
            },
            changeSelectAll() {
                let newValue = !this.selectAll;
                let checkboxes = {};
                this.forEachStore((storeId) => {
                    checkboxes[storeId] = newValue;
                });
                this.checkboxes = checkboxes;
            },
            deleteStores() {
                this.openModal('DeleteStore');
            },
            approveDelete() {
                Services.showLoader();
                Services.net().delete(this.route('merchantStore.deleteArray'), {
                    ids: this.selectedIds,
                }).then(() => {
                    this.closeModal('DeleteStore');
                    this.loadPage();
                    this.checkboxes = {};
                    Services.msg('Данные о ' + Helpers.plural_form(this.countSelected, this.formsPrepositional) +
                        ' успешно удалены.');
                }, () => {
                    Services.msg('Произошла ошибка при удалении данных о ' +
                        Helpers.plural_form(this.countSelected, this.formsPrepositional) + '.', 'danger');
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        computed: {
            editedShowColumns() {
                return this.columns.filter(function(column) {
                    return !column.isAlwaysShown;
                })
            },
            countSelected() {
                return Object.values(this.checkboxes).reduce((acc, val) => { return acc + val; }, 0);
            },
            selectedStores() {
                return this.stores.filter(store => {
                    return (store.id in this.checkboxes) && this.checkboxes[store.id];
                });
            },
            selectedIds() {
                return this.stores.filter(store => {
                    return (store.id in this.checkboxes) && this.checkboxes[store.id];
                }).map(store => store.id);
            },
        },
        watch: {
            currentPage() {
                this.loadPage();
            }
        }
    };
</script>