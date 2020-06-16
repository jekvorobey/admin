<template>
    <layout-main>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" @click="createType">Создать тип события</button>
            <div v-if="massAll(massSelectionType).length" class="action-bar d-flex justify-content-start">
                <span class="mr-4 align-self-baseline">Выбрано типов события: {{massAll(massSelectionType).length}}</span>
                <v-delete-button @delete="() => deleteTypes(massAll(massSelectionType))"/>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <td></td>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Код</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="type in types" :key="type.id">
                    <td>
                        <input type="checkbox"
                                :checked="massHas({type: massSelectionType, id: type.id})"
                                @change="e => massCheckbox(e, massSelectionType, type.id)">
                    </td>
                    <td>{{type.id}}</td>
                    <td>{{type.name}}</td>
                    <td>{{type.code}}</td>
                    
                    <td>
                        <v-delete-button @delete="() => deleteTypes([type.id])" class="float-right ml-1"/>
                        <button class="btn btn-warning float-right" @click="editType(type)">
                            <fa-icon icon="edit"></fa-icon>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div>
            <b-pagination
                    v-if="numPages !== 1"
                    v-model="page"
                    :total-rows="total"
                    :per-page="pageSize"
                    :hide-goto-end-buttons="numPages < 10"
                    class="mt-3 float-right"
            ></b-pagination>
        </div>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('TypeFormModal')">
                <div slot="header">
                    Тип события
                </div>
                <div slot="body">
                    <div class="form-group">
                        <v-input v-model="$v.form.name.$model" :error="errorName">Название*</v-input>
                        <v-input v-model="$v.form.code.$model" :error="errorCode">Код</v-input>
                    </div>
                    <div class="form-group">
                        <button @click="onSave" type="button" class="btn btn-primary">Сохранить</button>
                        <button @click="onCancel" type="button" class="btn btn-secondary">Отмена</button>
                    </div>
                </div>
            </modal>
        </transition>
    </layout-main>
</template>

<script>
    import withQuery from 'with-query';

    import { mapActions, mapGetters } from 'vuex';

    import {
        ACT_DELETE_TYPES,
        ACT_LOAD_PAGE,
        ACT_SAVE_TYPES,
        GET_LIST,
        GET_NUM_PAGES,
        GET_PAGE_NUMBER,
        GET_PAGE_SIZE,
        GET_TOTAL,
        NAMESPACE,
        SET_PAGE,
    } from '../../../store/modules/types';

    import modalMixin from '../../../mixins/modal';
    import mediaMixin from '../../../mixins/media';
    import massSelectionMixin from '../../../mixins/mass-selection';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Modal from '../../../components/controls/modal/modal.vue';
    import VInput from '../../../components/controls/VInput/VInput.vue';
    import FileInput from '../../../components/controls/FileInput/FileInput.vue';
    import VDeleteButton from '../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../scripts/services/services";

    export default {
        mixins: [
            modalMixin,
            mediaMixin,
            massSelectionMixin,
            validationMixin,
        ],
        components: {
            Modal,
            VInput,
            FileInput,
            VDeleteButton
        },
        props: {
            iTypes: {},
            iTotal: {},
            iCurrentPage: {},
        },
        data() {
            this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
                list: this.iTypes,
                total: this.iTotal,
                page: this.iCurrentPage
            });

            return {
                editTypedId: null,
                form: {
                    name: null,
                    code: null,
                },
                massSelectionType: 'types',
            };
        },
        validations: {
            form: {
                name: {required},
                code: {
                    pattern: (value) => /^[a-zA-Z0-9_]*$/.test(value)
                },
            }
        },
        methods: {
            ...mapActions(NAMESPACE, [
                ACT_LOAD_PAGE,
                ACT_SAVE_TYPES,
                ACT_DELETE_TYPES,
            ]),
            loadPage(page) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: page,
                }));

                return this[ACT_LOAD_PAGE]({page});
            },

            createType() {
                this.$v.form.$reset();
                this.editTypeId = null;
                this.form.name = null;
                this.form.code = null;
                this.openModal('TypeFormModal');
            },

            editType(type) {
                this.$v.form.$reset();
                this.editTypeId = type.id;
                this.form.name = type.name;
                this.form.code = type.code;
                this.openModal('TypeFormModal');
            },
            onSave() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.showLoader();
                this[ACT_SAVE_TYPES]({
                    id: this.editTypeId,
                    type: this.form
                }).then(() => {
                    return this[ACT_LOAD_PAGE]({page: this.page});
                }).finally(() => {
                    this.closeModal();
                    Services.hideLoader();
                });
            },
            onCancel() {
                this.closeModal();
            },
            deleteTypes(ids) {
                Services.showLoader();
                this[ACT_DELETE_TYPES]({ids})
                    .then(() => {
                        return this[ACT_LOAD_PAGE]({page: this.page});
                    })
                    .finally(() => {
                        Services.hideLoader();
                        this.massClear(this.massSelectionType);
                    });
            },
        },
        created() {
            window.onpopstate = () => {
                let query = qs.parse(document.location.search.substr(1));
                if (query.page) {
                    this.page = query.page;
                }
            };
        },
        computed: {
            ...mapGetters(NAMESPACE, {
                GET_PAGE_NUMBER,
                total: GET_TOTAL,
                pageSize: GET_PAGE_SIZE,
                numPages: GET_NUM_PAGES,
                types: GET_LIST,
            }),
            page: {
                get: function () {
                    return this.GET_PAGE_NUMBER;
                },
                set: function (page) {
                    this.loadPage(page);
                }
            },

            errorName() {
                if (this.$v.form.name.$dirty) {
                    if (!this.$v.form.name.required) return "Обязательное поле!";
                }
            },
            errorCode() {
                if (this.$v.form.code.$dirty) {
                    if (!this.$v.form.code.pattern) return "Только латиница, цифры и подчёркивание!";
                }
            },
        }
    };
</script>

<style scoped>
    th {
        vertical-align: top !important;
    }
    .with-small small{
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
