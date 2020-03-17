<template>
    <layout-main>
        <div class="mb-3">
            Всего меню: {{ pager.total }}.
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Код</th>
                    <th>Название</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="menu in menus">
                    <td>{{menu.id}}</td>
                    <td>{{menu.code}}</td>
                    <td>
                        <a :href="getRoute('menu.detail', {id: menu.id})">{{menu.name}}</a>
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
    </layout-main>
</template>

<script>

    import Services from '../../../../scripts/services/services';
    import withQuery from 'with-query';

    export default {
        components: {
        },
        props: {
            iMenus: {},
            iPager: {},
            iCurrentPage: {},
            iFilter: {},
            options: {}
        },
        data() {
            return {
                menus: this.iMenus,
                pager: this.iPager,
                currentPage: this.iCurrentPage || 1,
            };
        },
        methods: {
            changePage(newPage) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: newPage,
                    sort: this.sort
                }));
            },
            loadPage() {
                Services.net().get(this.route('menus.listPage'), {
                    page: this.currentPage,
                    sort: this.sort,
                }).then(data => {
                    this.menus = data.menus;
                    if (data.pager) {
                        this.pager = data.pager
                    }
                });
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
            }
        },
    };
</script>

<style scoped>
    th {
        vertical-align: top !important;
    }
</style>
