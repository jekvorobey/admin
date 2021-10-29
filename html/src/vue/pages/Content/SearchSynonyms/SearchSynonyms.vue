<template>
    <layout-main>
        <div class="row mb-3">
            <div class="col" style="text-align: left" v-if="canUpdate(blocks.content)">
                <button class="btn btn-success mr-1"
                    @click="onShowModalEdit(null)">
                    Добавить группу синонимов
                </button>
                <button class="btn btn-danger"
                        v-if="searchSynonyms.length"
                        :disabled="massEmpty(massSearchSynonymType)"
                        @click="onShowModalDelete()">
                    Удалить группы синонимов
                </button>
            </div>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <td v-if="searchSynonyms.length"></td>
                <th>Синонимы</th>
                <th v-if="canUpdate(blocks.content)">Действия</th>
            </tr>
            </thead>
            <tr v-for="searchSynonym in searchSynonyms">
                <td class="d-flex flex-column align-items-center">
                    <input type="checkbox"
                            :checked="massHas({type: massSearchSynonymType, id: searchSynonym.id})"
                            @change="e => massCheckbox(e, massSearchSynonymType, searchSynonym.id)"/>
                </td>
                <td>{{searchSynonym.synonyms}}</td>
                <td v-if="canUpdate(blocks.content)">
                    <button class="btn btn-info btn-md"
                            @click="onShowModalEdit(searchSynonym)">
                        <fa-icon icon="pencil-alt"/>
                    </button>
                </td>
            </tr>
            <tr v-if="!searchSynonyms.length">
                <td colspan="2">Синонимов нет</td>
            </tr>
        </table>
        <div>
            <b-pagination
                    v-if="pager.pages > 1"
                    v-model="currentPage"
                    :total-rows="pager.total"
                    :per-page="pager.pageSize"
                    @change="changePage"
                    :hide-goto-end-buttons="pager.pages < 10"
                    class="mt-3 float-right"
            ></b-pagination>
        </div>

        <b-modal id="modal-edit" size="lg" :title="(editSearchSynonym) ? 'Редактирование группы синонимов' :
        'Создание группы синонимов'" hide-footer>
            <div class="card">
                <div class="card-body">
                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="search-request-text">Группы синонимов через запятую</label>
                        </b-col>
                        <b-col cols="9">
                            <v-input id="search-request-text"
                                     v-model="editSearchSynonym.synonyms"
                                     :error="errText"
                                    tag="textarea"
                            ></v-input>
                        </b-col>
                    </b-row>
                    <button class="btn btn-success"
                            @click="saveSearchSynonym">
                        Сохранить
                    </button>
                    <button class="btn btn-outline-danger"
                            @click="onCloseModalEdit">
                        Отмена
                    </button>
                </div>
            </div>
        </b-modal>

        <b-modal id="modal-delete" title="Удаление запроса" hide-footer>
            <div class="card">
                <div class="card-body">
                    <div>
                        Вы уверены, что хотите удалить следующие группы синонимов: <br> {{massSearchSynonymNames()}}
                    </div>
                    <button class="btn btn-outline-danger"
                            @click="deleteSearchSynonyms">
                        Удалить
                    </button>
                    <button class="btn btn-success"
                            @click="onCloseModalDelete">
                        Отмена
                    </button>
                </div>
            </div>
        </b-modal>
    </layout-main>
</template>

<script>
    import VInput from '../../../components/controls/VInput/VInput.vue';
    import Services from '../../../../scripts/services/services';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';
    import massSelectionMixin from '../../../mixins/mass-selection';
    import withQuery from 'with-query';

    export default {
        components: {
            VInput,
        },
        mixins: [validationMixin, massSelectionMixin,],
        props: {
            iSearchSynonyms: Array,
            iPager: {},
            iCurrentPage: {},
        },
        data() {
            return {
                searchSynonyms: this.iSearchSynonyms || [],
                pager: this.iPager,
                currentPage: this.iCurrentPage || 1,
                editSearchSynonym: {
                    synonyms: '',
                },
                massActionType: null,
                massStatus: null,
                massSearchSynonymType: 'searchSynonyms',
            };
        },
        validations: {
            editSearchSynonym: {
                synonyms: {required},
            },
        },
        methods: {
            onShowModalEdit(searchSynonym) {
                if (searchSynonym) {
                    this.editSearchSynonym = Object.assign({}, searchSynonym);
                }
                this.$bvModal.show('modal-edit');
            },
            onCloseModalEdit() {
                this.editSearchSynonym = {
                    synonyms: '',
                };
                this.$v.$reset();
                this.$bvModal.hide('modal-edit');
            },
            saveSearchSynonym() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                // При обновлении //
                if (this.editSearchSynonym.id) {
                    Services.showLoader();
                    Services.net().put(
                        this.getRoute('searchSynonyms.update'),
                        {},
                        this.editSearchSynonym
                    ).then(
                        () => {
                            let foundIndex = this.searchSynonyms.findIndex(
                                item => item.id === this.editSearchSynonym.id
                            );
                            this.searchSynonyms[foundIndex].synonyms = this.editSearchSynonym.synonyms;
                            Services.msg("Изменения сохранены");
                        },
                        () => {
                            Services.msg("Не удалось сохранить изменения",'danger');
                        }
                    ).finally(() => {
                        Services.hideLoader();
                        this.onCloseModalEdit();
                    })
                }
                // При создании нового //
                else {
                    Services.showLoader();
                    Services.net().post(
                        this.getRoute('searchSynonyms.create'),
                        {},
                        this.editSearchSynonym
                    ).then(
                        data => {
                            this.searchSynonyms.push(data.search_synonym);
                            Services.msg("Изменения сохранены");
                        },
                        () => {
                            Services.msg("Не удалось сохранить изменения",'danger');
                        }
                    ).finally(() => {
                        Services.hideLoader();
                        this.onCloseModalEdit();
                    })
                }
            },
            onShowModalDelete() {
                this.$bvModal.show('modal-delete');
            },
            onCloseModalDelete() {
                this.$bvModal.hide('modal-delete');
            },
            massSearchSynonymNames() {
                return this.searchSynonyms.filter((searchSynonym) => {
                    return this.massAll(this.massSearchSynonymType).includes(searchSynonym.id);
                }).map((searchSynonym) => {
                    return searchSynonym.synonyms;
                }).join('; ');
            },
            deleteSearchSynonyms() {
                Services.showLoader();
                Services.net().delete(
                    this.getRoute('searchSynonyms.delete'),
                    {
                        ids: this.massAll(this.massSearchSynonymType),
                    }
                )
                    .then(() => {
                        this.searchSynonyms = this.searchSynonyms.filter((searchSynonym) => {
                            return !this.massAll(this.massSearchSynonymType).includes(searchSynonym.id);
                        });
                        this.massClear(this.massSearchSynonymType);
                        Services.msg("Группы синонимов успешно удалены");
                    }, () => {
                        Services.msg("Не удалось удалить группы синонимов",'danger');
                    }).finally(() => {
                    Services.hideLoader();
                })
                this.onCloseModalDelete();
            },
            changePage(newPage) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: newPage,
                }));
            },
            loadPage() {
                Services.net().get(this.route('searchSynonyms.page'), {
                    page: this.currentPage,
                }).then(data => {
                    this.searchSynonyms = data.searchSynonyms;
                    if (data.pager) {
                        this.pager = data.pager
                    }
                });
            },
        },
        computed: {
            errText() {
                if (this.$v.editSearchSynonym.synonyms.$dirty) {
                    if (!this.$v.editSearchSynonym.synonyms.required) {
                        return "Обязательное поле!";
                    }
                }
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
    }
</script>