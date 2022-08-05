<template>
    <layout-main>
        <div class="card">
            <div class="card-body">
                <file-input v-if="canUpdate(blocks.clients)" @uploaded="(data) => onFileUpload(data)"
                            class="mb-3"
                            destination="whitelist"
                            label="Загрузить вайтлист"
                ></file-input>
                <button class="btn btn-secondary" @click="downloadWhitelistLink()">
                    Выгрузить вайтлист
                </button>
            </div>
        </div>
        <b-card>
            <template v-slot:header>
                Фильтр
            </template>
            <div class="row">
                <f-select v-model="filter.is_user_exists" :options="booleanOptions" class="col-sm-12 col-md-2">
                    Есть ли user_id
                </f-select>
                <f-input v-model="filter.customer" class="col-sm-12 col-md-3 col-xl-5">
                    ФИО, e-mail, телефон или ссылка на профиль покупателя
                </f-input>
                <f-input v-model="filter.city" class="col-sm-12 col-md-3 col-xl-5">
                    Город
                </f-input>
                <f-input v-model="filter.comment" class="col-sm-12 col-md-3 col-xl-5">
                    Комментарий
                </f-input>
                <f-checkbox v-model="filter.is_possible_to_create_account" class="col-sm-12 col-md-3 col-xl-5">
                    Можно создать аккаунт
                </f-checkbox>
            </div>
            <template v-slot:footer>
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего: {{ pager.total }}.</span>
            </template>
        </b-card>
        <div class="d-flex justify-content-between mt-3 mb-3" v-if="canUpdate(blocks.clients)">
            <div>
                <b-btn @click="createAccounts()" :disabled="checkedValues.length == 0" class="btn btn-success mt-3">Перевести в юзеры</b-btn>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-condensed">
                <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all-page-orders" v-model="isSelectAllPageWhitelistItems">
                        <label for="select-all-page-orders" class="mb-0">Все</label>
                    </th>
                    <th v-for="column in columns" v-if="column.isShown">
                        <span v-html="column.name"></span>
                        <fa-icon v-if="column.description" icon="question-circle"
                                 v-b-popover.hover="column.description"></fa-icon>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="customerWhitelistItem in customerWhitelistItems">
                    <td>
                        <input type="checkbox"
                               class="whitelist-item-select"
                               v-if="isPossibleToCreateAccount(customerWhitelistItem)"
                               v-model="checkedValues"
                               :value="customerWhitelistItem.id">
                    </td>
                    <td v-for="column in columns" v-if="column.isShown">
                        <div v-html="column.value(customerWhitelistItem)"></div>
                    </td>
                </tr>
                <tr v-if="!customerWhitelistItems.length">
                    <td :colspan="columns.length + 1">Список пуст</td>
                </tr>
                </tbody>
            </table>
        </div>
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
import FileInput from '../../../components/controls/FileInput/FileInput.vue';
import FInput from '../../../components/filter/f-input.vue';
import FSelect from '../../../components/filter/f-select.vue';
import FCheckbox from '../../../components/filter/f-checkbox.vue';

import Services from "../../../../scripts/services/services";
import qs from "qs";
import Helpers from "../../../../scripts/helpers";
import {mapGetters} from "vuex";
import moment from 'moment';
import withQuery from 'with-query';

const cleanHiddenFilter = {};

const cleanFilter = Object.assign({
    customer: '',
    is_user_exists: null,
    city: '',
    comment: '',
    is_possible_to_create_account: false,
}, cleanHiddenFilter);

const serverKeys = [
    'customer',
    'is_user_exists',
    'city',
    'comment',
    'is_possible_to_create_account',
];

export default {
    props: [
        'iCustomerWhitelistItems',
        'iCurrentPage',
        'iPager',
        'iFilter',
        'iSort',
    ],
    components: {
        FCheckbox,
        FileInput,
        FInput,
        FSelect,
    },
    data() {
        let self = this;
        let filter = Object.assign({}, cleanFilter, this.iFilter);
        return {
            opened: false,
            currentPage: this.iCurrentPage,
            customerWhitelistItems: this.iCustomerWhitelistItems,
            filter,
            sort: this.iSort,
            appliedFilter: {},
            pager: this.iPager,
            isSelectAllPageWhitelistItems: false,
            checkedValues: [],
            columns: [
                {
                    name: 'ID',
                    code: 'id',
                    value: function (customerWhitelistItem) {
                        return  customerWhitelistItem.id;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'ФИО мастера',
                    code: 'fio',
                    value: function (customerWhitelistItem) {
                        return  [customerWhitelistItem.last_name, customerWhitelistItem.first_name, customerWhitelistItem.middle_name].join(' ');
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Номер телефона',
                    code: 'phone',
                    value: function(customerWhitelistItem) {
                        return customerWhitelistItem.phone;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Email',
                    code: 'email',
                    value: function(customerWhitelistItem) {
                        return customerWhitelistItem.email;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Ссылка на профиль',
                    code: 'link',
                    value: function(customerWhitelistItem) {
                        if (!customerWhitelistItem.link) {
                            return;
                        }
                        return `<a href="${self.getLink(customerWhitelistItem.link)}" target="_blank">${customerWhitelistItem.link}</a>`;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Город',
                    code: 'city',
                    value: function(customerWhitelistItem) {
                        return customerWhitelistItem.city;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Сфера деятельности',
                    code: 'activity',
                    value: function(customerWhitelistItem) {
                        return customerWhitelistItem.activity;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'User ID',
                    code: 'user_id',
                    value: function(customerWhitelistItem) {
                        return customerWhitelistItem.user_id;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Комментарий',
                    code: 'comment',
                    value: function(customerWhitelistItem) {
                        return customerWhitelistItem.comment;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Создано',
                    code: 'created_at',
                    value: function(customerWhitelistItem) {
                        return self.dateTimeToStr(customerWhitelistItem.created_at);
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Обновлено',
                    code: 'updated_at',
                    value: function(customerWhitelistItem) {
                        return self.dateTimeToStr(customerWhitelistItem.updated_at);
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
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
        onFileUpload(data) {
            Services.showLoader();
            Services.net().post(this.getRoute('customers.whitelist.import'), {
                file_id: data.id,
            }).then((data) => {
                Services.msg('Импорт выполнен успешно');
                this.applyFilter();
            }).finally(() => {
                Services.hideLoader();
            });
        },
        downloadWhitelistLink() {
            window.open(this.getRoute('customers.whitelist.export'));
        },
        loadPage() {
            Services.showLoader();
            Services.net().get(this.route('customers.whitelist.pagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
                sort: this.sort,
            }).then(data => {
                this.customerWhitelistItems = data.customerWhitelistItems;
                if (data.pager) {
                    this.pager = data.pager;
                }
                this.isSelectAllPageWhitelistItems = false;
                this.checkedValues = [];
            }).finally(() => {
                Services.hideLoader();
            });
        },
        dateTimeToStr(datetime) {
            return moment(datetime).format('DD.MM.YYYY HH:mm')
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
        getLink(link) {
            return link.startsWith('http') ? link : 'https://' + link;
        },
        updateAllPageWhitelistItems() {
            if (this.isSelectAllPageWhitelistItems) {
                let checkboxes = document.getElementsByClassName('whitelist-item-select');
                this.checkedValues = [];
                for (let i = 0; i < checkboxes.length; i++) {
                    this.checkedValues.push(checkboxes[i].value);
                }
            } else {
                this.checkedValues = [];
            }
        },
        createAccounts() {
            if (!this.checkedValues) {
                return;
            }
            Services.showLoader();
            Services.net().post(this.route('customers.whitelist.create_accounts'), {
                whitelistIds: this.checkedValues
            }).then(data => {
                Services.msg('Пользователи добавлены в очередь на создание аккаунтов');
                this.applyFilter();
            }).finally(() => {
                Services.hideLoader();
            });
        },
        isPossibleToCreateAccount(customerWhitelistItem) {
            return customerWhitelistItem.phone && !customerWhitelistItem.user_id;
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        booleanOptions() {
            return [
                {value: true, text: 'Да'},
                {value: false, text: 'Нет'},
            ];
        },
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
        },
        isSelectAllPageWhitelistItems() {
            this.updateAllPageWhitelistItems();
        }
    },
};
</script>
<style scoped>
    .additional-filter {
        border-top: 1px solid #DFDFDF;
    }
</style>