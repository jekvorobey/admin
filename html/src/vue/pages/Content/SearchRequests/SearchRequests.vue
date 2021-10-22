<template>
    <layout-main>
        <div class="row mb-3">
            <div class="col-6" style="text-align: left" v-if="canUpdate(blocks.content)">
                <button class="btn btn-success mr-1"
                    @click="onShowModalEdit(null)">
                    Добавить запрос
                </button>
                <button class="btn btn-danger"
                        :disabled="massEmpty(massSearchRequestType)"
                        @click="onShowModalDelete()">
                    Удалить запросы
                </button>
            </div>
            <div class="col-6" style="text-align: right" v-if="canUpdate(blocks.content)">
                <button v-if="itemsOrder.length > 0"
                        class="btn btn-dark"
                        @click="reorderItems">
                    <template v-if="!isReordering">
                        <fa-icon icon="save"/> Сохранить порядок
                    </template>
                    <template v-else>
                            <span class="spinner-border"
                                  style="width: 1.5rem; height: 1.5rem;"
                                  role="status"
                                  aria-hidden="true">
                            </span>
                        Порядок сохраняется...
                    </template>
                </button>
                <button v-else
                        class="btn btn-light"
                        disabled>
                    <fa-icon icon="check"/> Порядок сохранён
                </button>
            </div>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <td></td>
                <th>Текст запроса</th>
                <th v-if="canUpdate(blocks.content)">Действия</th>
            </tr>
            </thead>
                <draggable v-model="searchRequests"
                           v-bind="dragOptions"
                           style="cursor: move"
                           tag="tbody"
                >
                    <tr v-for="searchRequest in searchRequests">
                        <td class="d-flex flex-column align-items-center">
                            <input type="checkbox"
                                   :checked="massHas({type: massSearchRequestType, id: searchRequest.id})"
                                   @change="e => massCheckbox(e, massSearchRequestType, searchRequest.id)"/>
                        </td>
                        <td>{{searchRequest.text}}</td>
                        <td v-if="canUpdate(blocks.content)">
                            <button class="btn btn-info btn-md"
                                    @click="onShowModalEdit(searchRequest)">
                                <fa-icon icon="pencil-alt"/>
                            </button>
                        </td>
                    </tr>
                </draggable>
        </table>

        <b-modal id="modal-edit" :title="(editSearchRequest) ? 'Редактирование запроса' : 'Создание запроса'" hide-footer>
            <div class="card">
                <div class="card-body">
                    <b-row class="mb-2">
                        <b-col cols="3">
                            <label for="search-request-text">Текст запроса</label>
                        </b-col>
                        <b-col cols="9">
                            <v-input id="search-request-text"
                                     v-model="editSearchRequest.text"
                                     :error="errText"
                            ></v-input>
                        </b-col>
                    </b-row>
                    <button class="btn btn-success"
                            @click="saveSearchRequest">
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
                        Вы уверены, что хотите удалить следующие запросы: <br>
                        {{massSearchRequestNames()}}
                    </div>
                    <button class="btn btn-outline-danger"
                            @click="deleteSearchRequests">
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
    import draggable from 'vuedraggable';
    import VInput from '../../../components/controls/VInput/VInput.vue';
    import Services from '../../../../scripts/services/services';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';
    import massSelectionMixin from '../../../mixins/mass-selection';

    export default {
        components: {
            draggable,
            VInput,
        },
        mixins: [validationMixin, massSelectionMixin,],
        props: {
            iSearchRequests: Array,
            dragOptions: {
                animation: 200,
                sort: true,
            },
        },
        data() {
            return {
                searchRequests: this.iSearchRequests,
                editSearchRequest: {
                    text: '',
                },
                massActionType: null,
                massStatus: null,
                massSearchRequestType: 'searchRequests',
                itemsOrder: [],
                isReordering: false,
            };
        },
        validations: {
            editSearchRequest: {
                text: {required},
            },
        },
        methods: {
            onShowModalEdit(searchRequest) {
                if (searchRequest) {
                    this.editSearchRequest = Object.assign({}, searchRequest);
                }
                this.$bvModal.show('modal-edit');
            },
            onCloseModalEdit() {
                this.editSearchRequest = {
                    text: '',
                };
                this.$v.$reset();
                this.$bvModal.hide('modal-edit');
            },
            /**
             * Добавить новый поисковый запрос или обновить старый
             */
            saveSearchRequest() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                // При обновлении запроса //
                if (this.editSearchRequest.id) {
                    Services.showLoader();
                    Services.net().put(
                        this.getRoute('searchRequests.update'),
                        {},
                        this.editSearchRequest
                    ).then(
                        () => {
                            let foundIndex = this.searchRequests.findIndex(
                                item => item.id === this.editSearchRequest.id
                            );
                            this.searchRequests[foundIndex].text = this.editSearchRequest.text;
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
                // При создании нового запроса //
                else {
                    Services.showLoader();
                    Services.net().post(
                        this.getRoute('searchRequests.create'),
                        {},
                        this.editSearchRequest
                    ).then(
                        data => {
                            this.searchRequests.push(data.search_request);
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
            massSearchRequestNames() {
                return this.searchRequests.filter((searchRequest) => {
                    return this.massAll(this.massSearchRequestType).includes(searchRequest.id);
                }).map((searchRequest) => {
                    return searchRequest.text;
                }).join(',');
            },
            /**
             * Удалить поисковый запрос
             */
            deleteSearchRequests() {
                Services.showLoader();
                Services.net().delete(
                    this.getRoute('searchRequests.delete'),
                    {
                        ids: this.massAll(this.massSearchRequestType),
                    }
                )
                    .then(() => {
                        this.searchRequests = this.searchRequests.filter((searchRequest) => {
                            return !this.massAll(this.massSearchRequestType).includes(searchRequest.id);
                        });
                        this.massClear(this.massSearchRequestType);
                        Services.msg("Поисковый запрос успешно удален");
                    }, () => {
                        Services.msg("Не удалось удалить поисковый запрос",'danger');
                    }).finally(() => {
                    Services.hideLoader();
                })
                this.onCloseModalDelete();
            },
            // Изменить порядок поисковых запросов и сохранить на сервере //
            reorderItems() {
                this.isReordering = true;
                Services.net().put(
                    this.getRoute('searchRequests.reorder'),
                    {},
                    {
                        items: this.itemsOrder
                    }
                ).then(
                    () => {
                        Services.msg("Новый порядок сохранён");
                        this.itemsOrder = [];
                    },
                    () => {
                        Services.msg("Не удалось сохранить изменения",'danger');
                    }
                ).finally(() => {
                    this.isReordering = false;
                })
            },
        },
        computed: {
            errText() {
                if (this.$v.editSearchRequest.text.$dirty) {
                    if (!this.$v.editSearchRequest.text.required) {
                        return "Обязательное поле!";
                    }
                }
            },
        },
        watch: {
            // Пересортировка поисковых запросов //
            'searchRequests': {
                handler() {
                    this.itemsOrder = Object.values(this.searchRequests)
                        .map((item, index) => ({
                                id: item.id,
                                order_num: index + 1,
                            })
                        );
                }
            },
        },
    }
</script>

<style scoped>

</style>