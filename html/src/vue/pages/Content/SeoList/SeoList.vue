<template>
    <layout-main>
        <div class="mb-3">
            Всего SEO страниц: {{ pager.total }}.
        </div>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Код страницы</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="seo in seos">
                <td>{{ seo.id }}</td>
                <td v-if="canUpdate(blocks.content)" class="with-small">
                    <a :href="getRoute('seo.updatePage', {id: seo.id})">{{ seo.name }}</a>
                </td>
                <td class="with-small">
                    {{seo.code}}
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

    import { mapActions } from 'vuex';

    export default {
        props: {
            iSeo: {},
            iPager: {},
            iCurrentPage: {},
            options: {}
        },
        data() {
            return {
                seos: this.iSeo,
                pager: this.iPager,
                currentPage: this.iCurrentPage || 1,
            };
        },
        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            changePage(newPage) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: newPage,
                }));
            },
            loadPage() {
                Services.net().get(this.route('seo.page'), {
                    page: this.currentPage,
                }).then(data => {
                    this.seos = data.seos;
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
        }
    };
</script>

<style scoped>
    th {
        vertical-align: top !important;
    }
</style>
