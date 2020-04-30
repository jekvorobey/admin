<template>
    <layout-main>
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th v-for="column in columns" v-if="column.isShown">
                        {{column.name}}
                        <fa-icon v-if="column.description" icon="question-circle"
                                v-b-popover.hover="column.description"></fa-icon>
                    </th>
                    <th>
                        <button class="btn btn-light float-right" @click="showChangeColumns">
                            <fa-icon icon="cog"></fa-icon>
                        </button>
                        <modal-columns :i-columns="editedShowColumns"></modal-columns>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="orderStatus in orderStatuses">
                    <td v-for="column in columns" v-if="column.isShown" v-html="column.value(orderStatus)"></td>
                    <td></td>
                </tr>
                <tr v-if="!orderStatuses.length">
                    <td :colspan="columns.length">Статусов заказов нет</td>
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
    import ModalColumns from '../../../../components/modal-columns/modal-columns.vue';
    import modalMixin from '../../../../mixins/modal';

    export default {
    props: [
        'iOrderStatuses',
        'iCurrentPage',
        'iPager',
    ],
    components: {
        ModalColumns,
    },
    mixins: [modalMixin],
    data() {
        return {
            currentPage: this.iCurrentPage,
            orderStatuses: this.iOrderStatuses,
            pager: this.iPager,
            columns: [
                {
                    name: 'ID',
                    code: 'id',
                    value: function(orderStatus) {
                        return orderStatus.id;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Название',
                    code: 'name',
                    value: function(orderStatus) {
                        return orderStatus.name;
                    },
                    isShown: true,
                    isAlwaysShown: true,
                },
                {
                    name: 'Описание',
                    description: 'Все Отправления данного Заказа были переведены в статус /// или Смысл статуса если оно не зависит от Отправлений',
                    code: 'description',
                    value: function(orderStatus) {
                        return orderStatus.description;
                    },
                    isShown: true,
                    isAlwaysShown: false,
                },
                {
                    name: 'Название для клиента',
                    description: 'Что видит клиент в статусе сущности ЗАКАЗ',
                    code: 'display_name',
                    value: function(orderStatus) {
                        return orderStatus.display_name;
                    },
                    isShown: true,
                    isAlwaysShown: false,
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
        loadPage() {
            Service.net().get(this.route('orderStatuses.pagination'), {
                page: this.currentPage,
                filter: this.appliedFilter,
                sort: this.sort,
            }).then(data => {
                this.orderStatuses = data.orderStatuses;
                if (data.pager) {
                    this.pager = data.pager
                }
            });
        },
        showChangeColumns() {
            this.openModal('list_columns');
        },
    },
    computed: {
        editedShowColumns() {
            return this.columns.filter(function(column) {
                return !column.isAlwaysShown;
            })
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
    }
};
</script>