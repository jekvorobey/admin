<template>
    <layout-main>
        <div class="mt-3 mb-3 shadow p-3">
            <div class="row">
                <f-input v-model="filter.id" class="col-lg-3 col-md-6 col-sm-12">ID</f-input>
                <f-select
                        v-model="filter.type"
                        :options="typeOptions"
                        class="col-lg-3 col-md-6 col-sm-12"
                >
                    Тип
                </f-select>
                <f-input v-model="filter.name" class="col-lg-3 col-md-6 col-sm-12">Название</f-input>
                <f-select
                        v-model="filter.active"
                        :options="activeOptions"
                        class="col-lg-3 col-md-6 col-sm-12"
                >
                    Видимость
                </f-select>
            </div>
            <button @click="applyFilter" class="btn btn-dark">Применить</button>
            <button @click="clearFilter" class="btn btn-secondary">Очистить</button>
        </div>
        <div class="mb-3" v-if="canUpdate(blocks.content)">
            <button @click="goToCreatePage" class="btn btn-success">Создать</button>
        </div>
        <div class="mb-3">
            Всего подборок: {{ pager.total }}.
        </div>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Видимость</th>
                <th>Изображение</th>
                <th>Название</th>
                <th>Тип</th>
                <th v-if="canUpdate(blocks.content)"><!-- Кнопки --></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="productGroup in productGroups">
                <td>{{productGroup.id}}</td>
                <td>
                    <b-badge v-if="productGroup.active" variant="success">
                        Активна
                    </b-badge>
                    <b-badge v-if="!productGroup.active" variant="danger">
                        Деактивирована
                    </b-badge>
                    <br>
                    <b-badge v-if="!productGroup.is_shown && productGroup.active" variant="warning">
                        Только по прямой ссылке
                    </b-badge>
                </td>
                <td><img :src="productGroup.photo ? productGroup.photo : '//placehold.it/75x50?text=No+image'"
                         class="preview"></td>
                <td class="with-small">
                    <a :href="getRoute('productGroup.updatePage', {id: productGroup.id})">{{productGroup.name}}</a>
                </td>
                <td>{{productGroup.type.name}}</td>
                <td v-if="canUpdate(blocks.content)">
                    <b-button class="btn btn-danger btn-sm">
                        <fa-icon icon="trash-alt"
                                 @click="removeItem(productGroup.id)"/>
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
        type: '',
    };

    export default {
        components: {
            FSelect,
            FInput,
        },
        props: {
            iProductGroups: {},
            iPager: {},
            iCurrentPage: {},
            iFilter: {},
            options: {}
        },
        data() {
            let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);

            return {
                productGroups: this.iProductGroups,
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
                window.location.href = this.route('productGroup.createPage');
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
                Services.net().get(this.route('productGroups.page'), {
                    page: this.currentPage,
                    filter: this.filter,
                    //sort: this.sort,
                }).then(data => {
                    this.productGroups = data.productGroups;
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
                    .delete(this.getRoute('productGroup.delete', {id: id,}))
                    .then((data) => {
                        this.showMessageBox({title: 'Элемент удалён'});
                        this.loadPage();
                    })
                    .catch(() => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
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
        },
        computed: {
            typeOptions() {
                return this.options.types.map(type => ({value: type.id, text: type.name}));
            },
            activeOptions() {
                return [
                    {
                        value: 0,
                        text: 'Деактивирована',
                    },
                    {
                        value: 1,
                        text: 'Активна',
                    },
                ];
            },
        }
    };
</script>

<style scoped>
    th {
        vertical-align: top !important;
    }

    .with-small small {
        display: block;
        color: gray;
        line-height: 1rem;
        overflow: hidden;
    }

    .preview {
        height: 50px;
        border-radius: 5px;
    }
</style>
