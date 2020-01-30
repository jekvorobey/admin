<template>
    <layout-main>
        <div class="mb-3">
            <button @click="openModal('userAdd')" class="btn btn-success"><fa-icon icon="plus"/> Добавить пользователя</button>
            <span class="float-right">Всего пользователей: {{ pager.total }}. <span v-if="selectedItems.length">Выбрано: {{selectedItems.length}}</span></span>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th></th>
                <th>№</th>
                <th>Login</th>
                <th>Система</th>
                <th>E-mail подтверждён</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in users">
                <td>
                    <input type="checkbox" :checked="itemSelected(user.id)"
                           @change="e => selectItem(e, user.id)">
                </td>
                <td>{{ user.id }}</td>
                <td><a :href="getRoute('settings.userDetail', {id: user.id})">{{ user.login }}</a></td>
                <td>{{ frontName(user.front) }}</td>
                <td><span class="badge" :class="{'badge-success': user.email_verified, 'badge-danger': !user.email_verified}">{{ user.email_verified ? 'Да' : 'Нет' }}</span></td>
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
        <user-add-modal :fronts="options.fronts" @onSave="onUserCreated"></user-add-modal>
    </layout-main>
</template>

<script>

    import Services from '../../../../scripts/services/services';
    import withQuery from 'with-query';

    import UserAddModal from '../components/user-add-modal.vue';

    import { mapGetters } from 'vuex';
    import modalMixin from '../../../mixins/modal.js';

    export default {
        mixins: [modalMixin],
        components: {
            UserAddModal
        },
        props: {
            iUsers: {},
            iPager: {},
            iCurrentPage: {},
            options: {}
        },
        data() {
            return {
                users: this.iUsers,
                pager: this.iPager,
                currentPage: this.iCurrentPage || 1,
                selectedItems: []
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
                Services.net().get(this.route('settings.userListPagination'), {
                    page: this.currentPage,
                    filter: this.filter,
                    //sort: this.sort,
                }).then(data => {
                    this.users = data.items;
                    if (data.pager) {
                        this.pager = data.pager
                    }
                });
            },
            itemSelected(id) {
                return this.selectedItems.indexOf(id) !== -1;
            },
            selectItem(e, id) {
                if (e.target.checked) {
                    this.selectedItems.push(id);
                } else {
                    let index = this.selectedItems.indexOf(id);
                    if (index !== -1) {
                        this.selectedItems.splice(index, 1);
                    }
                }
            },
            frontName(id) {
                let fronts = Object.values(this.options.fronts).filter(front => front.id === id);
                return fronts.length > 0 ? fronts[0].name : 'N/A';
            },
            onUserCreated() {
                this.showMessageBox({text: "Пользователь создан!"});
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
            ...mapGetters(['getRoute']),
        }
    };
</script>
