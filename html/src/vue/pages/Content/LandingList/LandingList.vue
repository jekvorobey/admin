<template>
    <layout-main>
        <div class="mt-3 mb-3 shadow p-3">
            <div class="row">
                <f-input v-model="filter.id" class="col-lg-3 col-md-6 col-sm-12">ID</f-input>
            </div>
            <button @click="applyFilter" class="btn btn-dark">Применить</button>
            <button @click="clearFilter" class="btn btn-secondary">Очистить</button>
        </div>
        <div class="mb-3" v-if="canUpdate(blocks.content)">
            <button @click="goToCreatePage" class="btn btn-success">Создать</button>
        </div>
        <div class="mb-3">
            Всего баннеров: {{ pager.total }}.
        </div>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Видимость</th>
                <th>Название</th>
                <th>Предпросмотр</th>
                <th v-if="canUpdate(blocks.content)"><!-- Кнопки --></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="landing in landings">
                <td>{{landing.id}}</td>
                <td>
                    <b-badge v-if="landing.active" variant="success">
                        Активен
                    </b-badge>
                    <b-badge v-if="!landing.active" variant="danger">
                        Деактивирован
                    </b-badge>
                    <br>
                </td>
                <td v-if="canUpdate(blocks.content)" class="with-small">
                    <a :href="getRoute('landing.updatePage', {id: landing.id})">{{landing.name}}</a>
                </td>
                <td v-else class="with-small">
                    {{landing.name}}
                </td>
                <td>
                    <a target="_blank" :href="getUrl(landing.code)">Предпросмотр</a>
                </td>
                <td v-if="canUpdate(blocks.content)">
                    <b-button class="btn btn-danger btn-sm">
                        <fa-icon icon="trash-alt"
                                 @click="removeItem(landing.id)"/>
                    </b-button>
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

    import FSelect from '../../../components/filter/f-select.vue';
    import FInput from '../../../components/filter/f-input.vue';
    import { mapActions } from 'vuex';

    const cleanFilter = {
        id: '',
    };

    export default {
        components: {
            FSelect,
            FInput,
        },
        props: {
            iLandings: {},
            iPager: {},
            iCurrentPage: {},
            iFilter: {},
            url: String,
            options: {}
        },
        data() {
            let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
            return {
                landings: this.iLandings,
                pager: this.iPager,
                currentPage: this.iCurrentPage || 1,
                filter,
            };
        },
        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            goToCreatePage() {
                window.location.href = this.route('landing.createPage');
            },
            changePage(newPage) {
                let cleanFilter = {};
                for (let [key, value] of Object.entries(this.filter)) {
                    if (value) {
                        cleanFilter[key] = value;
                    }
                }
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: newPage,
                    filter: cleanFilter,
                    //sort: this.sort
                }));
            },
            loadPage() {
                Services.net().get(this.route('landing.page'), {
                    page: this.currentPage,
                    filter: this.filter,
                    //sort: this.sort,
                }).then(data => {
                    this.landings = data.landings;
                    if (data.pager) {
                        this.pager = data.pager
                    }
                });
            },
            applyFilter() {
                this.changePage(1);
                this.loadPage();
            },
            clearFilter() {
                this.$set(this, 'filter', JSON.parse(JSON.stringify(cleanFilter)));
                this.applyFilter();
            },
            removeItem(id) {
                Services.net()
                    .delete(this.getRoute('landing.delete', {id: id,}))
                    .then((data) => {
                        this.showMessageBox({title: 'Элемент удалён'});
                        this.loadPage();
                    })
                    .catch(() => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    });
            },
            getUrl(code) {
                return this.url + '/pages/' + code;
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
            typeOptions() {
                return this.options.types.map(type => ({value: type.id, text: type.name}));
            },
        }
    };
</script>

<style scoped>
    th {
        vertical-align: top !important;
    }
</style>
